<?php

namespace App\Http\Controllers;

use App\Http\Requests\MakeMoveRequest;
use App\Http\Requests\StoreGameRequest;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GameController extends Controller
{
    private const WINNING_LINES = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8], // Filas
        [0, 3, 6], [1, 4, 7], [2, 5, 8], // Columnas
        [0, 4, 8], [2, 4, 6],            // Diagonales
    ];

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $availableGames = Game::query()
            ->with('playerX')
            ->where('status', 'esperando')
            ->where('player_x_id', '!=', $user->id)
            ->latest()
            ->get();

        $myActiveGames = Game::query()
            ->with(['playerX', 'playerO', 'currentTurnUser'])
            ->whereIn('status', ['esperando', 'en_curso'])
            ->where(function ($q) use ($user) {
                $q->where('player_x_id', $user->id)
                    ->orWhere('player_o_id', $user->id);
            })
            ->latest()
            ->get();

        $allActiveGames = $user->isAdmin()
            ? Game::query()
                ->with(['playerX', 'playerO', 'currentTurnUser'])
                ->whereIn('status', ['esperando', 'en_curso'])
                ->latest()
                ->get()
            : collect();

        $players = User::query()
            ->where('role', 'jugador')
            ->orderBy('name')
            ->get();

        return view('games.index', [
            'availableGames' => $availableGames,
            'myActiveGames' => $myActiveGames,
            'allActiveGames' => $allActiveGames,
            'players' => $players,
            'isAdmin' => $user->isAdmin(),
            'sessionId' => $request->session()->getId(),
        ]);
    }

    public function history(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $games = Game::query()
            ->with(['playerX', 'playerO', 'winner'])
            ->when(! $user->isAdmin(), function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('player_x_id', $user->id)
                        ->orWhere('player_o_id', $user->id);
                });
            })
            ->latest('updated_at')
            ->get();

        return view('games.history', [
            'games' => $games,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    public function store(StoreGameRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validated();

        $game = Game::create([
            'title' => ! empty($validated['title'])
                ? trim($validated['title'])
                : 'Partida de ' . $user->name,
            'player_x_id' => $user->id,
            'player_o_id' => null,
            'current_turn_user_id' => $user->id,
            'current_symbol' => 'X',
            'board' => array_fill(0, 9, null),
            'status' => 'esperando',
            'winner_id' => null,
            'winning_symbol' => null,
        ]);

        return redirect()->route('games.show', $game)
            ->with('success', 'Partida creada. Eres el jugador X; espera a que otro jugador se una.');
    }

    public function join(Game $game): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $result = DB::transaction(function () use ($game, $user) {
            /** @var Game $lockedGame */
            $lockedGame = Game::query()->whereKey($game->id)->lockForUpdate()->firstOrFail();

            if ((int) $lockedGame->player_x_id === (int) $user->id) {
                return ['ok' => false, 'message' => 'No puedes unirte a tu propia partida como jugador O.'];
            }

            if ($lockedGame->status !== 'esperando' || $lockedGame->player_o_id !== null) {
                return ['ok' => false, 'message' => 'Esta partida ya no está disponible para unirse.'];
            }

            $lockedGame->update([
                'player_o_id' => $user->id,
                'current_turn_user_id' => $lockedGame->player_x_id,
                'current_symbol' => 'X',
                'status' => 'en_curso',
            ]);

            return ['ok' => true, 'game' => $lockedGame];
        });

        if (! $result['ok']) {
            return redirect()->route('games.index')
                ->withErrors(['game' => $result['message']]);
        }

        return redirect()->route('games.show', $result['game'])
            ->with('success', 'Te uniste a la partida como jugador O. ¡Comienza el turno de X!');
    }

    public function show(Game $game, Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAdmin() && ! $game->isParticipant($user->id)) {
            abort(403, 'No participas en esta partida.');
        }

        $game->load(['playerX', 'playerO', 'currentTurnUser', 'winner']);

        return view('games.show', [
            'game' => $game,
            'mySymbol' => $game->symbolForUser($user->id),
            'isAdmin' => $user->isAdmin(),
            'sessionId' => $request->session()->getId(),
        ]);
    }

    public function state(Game $game): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAdmin() && ! $game->isParticipant($user->id)) {
            abort(403);
        }

        $game->load(['playerX', 'playerO', 'currentTurnUser', 'winner']);

        return response()->json([
            'id' => $game->id,
            'board' => $game->board,
            'status' => $game->status,
            'current_symbol' => $game->current_symbol,
            'current_turn_user_id' => $game->current_turn_user_id,
            'current_turn_name' => $game->currentTurnUser?->name,
            'is_my_turn' => (int) $game->current_turn_user_id === (int) $user->id && $game->status === 'en_curso',
            'winner_id' => $game->winner_id,
            'winner_name' => $game->winner?->name,
            'winning_symbol' => $game->winning_symbol,
            'player_x_name' => $game->playerX?->name,
            'player_o_name' => $game->playerO?->name,
        ]);
    }

    public function move(MakeMoveRequest $request, Game $game): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $cell = (int) $request->validated()['cell'];

        $result = DB::transaction(function () use ($game, $user, $cell) {
            /** @var Game $lockedGame */
            $lockedGame = Game::query()->whereKey($game->id)->lockForUpdate()->firstOrFail();

            if ($lockedGame->status !== 'en_curso') {
                return ['ok' => false, 'message' => 'La partida no está en curso.'];
            }

            if ($lockedGame->player_o_id === null) {
                return ['ok' => false, 'message' => 'Aún falta que se una el segundo jugador (O).'];
            }

            $userSymbol = $lockedGame->symbolForUser($user->id);
            if ($userSymbol === null) {
                return ['ok' => false, 'message' => 'No eres participante de esta partida.'];
            }

            if ((int) $lockedGame->current_turn_user_id !== (int) $user->id || $lockedGame->current_symbol !== $userSymbol) {
                return ['ok' => false, 'message' => 'No es tu turno. Espera a que juegue el otro jugador.'];
            }

            $board = $lockedGame->board ?? array_fill(0, 9, null);
            if (($board[$cell] ?? null) !== null) {
                return ['ok' => false, 'message' => 'Esa casilla ya está ocupada.'];
            }

            $board[$cell] = $userSymbol;
            $winnerSymbol = $this->checkWinner($board);

            if ($winnerSymbol !== null) {
                $winnerId = $winnerSymbol === 'X' ? $lockedGame->player_x_id : $lockedGame->player_o_id;
                $lockedGame->update([
                    'board' => $board,
                    'status' => 'ganado',
                    'winner_id' => $winnerId,
                    'winning_symbol' => $winnerSymbol,
                    'current_turn_user_id' => null,
                ]);

                return ['ok' => true, 'message' => "¡Victoria para {$user->name} ({$winnerSymbol})!"];
            }

            if (! in_array(null, $board, true)) {
                $lockedGame->update([
                    'board' => $board,
                    'status' => 'empate',
                    'winner_id' => null,
                    'winning_symbol' => null,
                    'current_turn_user_id' => null,
                ]);

                return ['ok' => true, 'message' => 'La partida terminó en empate.'];
            }

            $nextSymbol = $userSymbol === 'X' ? 'O' : 'X';
            $nextUserId = $nextSymbol === 'X' ? $lockedGame->player_x_id : $lockedGame->player_o_id;

            $lockedGame->update([
                'board' => $board,
                'current_symbol' => $nextSymbol,
                'current_turn_user_id' => $nextUserId,
            ]);

            return ['ok' => true, 'message' => null];
        });

        if (! $result['ok']) {
            return back()->withErrors(['move' => $result['message']]);
        }

        return redirect()->route('games.show', $game)
            ->when(! empty($result['message']), fn ($r) => $r->with('success', $result['message']));
    }

    public function abandon(Game $game): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $game->isParticipant($user->id)) {
            abort(403, 'Solo los participantes pueden abandonar esta partida.');
        }

        DB::transaction(function () use ($game, $user) {
            /** @var Game $lockedGame */
            $lockedGame = Game::query()->whereKey($game->id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedGame->status, ['esperando', 'en_curso'], true)) {
                return;
            }

            $winnerId = null;
            if ($lockedGame->status === 'en_curso') {
                $winnerId = (int) $lockedGame->player_x_id === (int) $user->id
                    ? $lockedGame->player_o_id
                    : $lockedGame->player_x_id;
            }

            $lockedGame->update([
                'status' => 'abandonada',
                'winner_id' => $winnerId,
                'current_turn_user_id' => null,
            ]);
        });

        return redirect()->route('games.index')
            ->with('success', 'Has abandonado la partida.');
    }

    public function cancel(Game $game): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAdmin()) {
            abort(403, 'Solo el administrador puede cancelar partidas.');
        }

        DB::transaction(function () use ($game) {
            /** @var Game $lockedGame */
            $lockedGame = Game::query()->whereKey($game->id)->lockForUpdate()->firstOrFail();

            if (in_array($lockedGame->status, ['esperando', 'en_curso'], true)) {
                $lockedGame->update([
                    'status' => 'cancelada',
                    'current_turn_user_id' => null,
                ]);
            }
        });

        return redirect()->route('games.index')
            ->with('success', 'Partida cancelada por el administrador.');
    }

    private function checkWinner(array $board): ?string
    {
        foreach (self::WINNING_LINES as [$a, $b, $c]) {
            if ($board[$a] !== null && $board[$a] === $board[$b] && $board[$a] === $board[$c]) {
                return $board[$a];
            }
        }

        return null;
    }
}