@extends('layouts.app')

@section('title', 'Tablero · Partida #' . $game->id)

@section('content')
<style>
    .board-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        max-width: 360px;
        margin: 0 auto;
    }
    .cell-btn {
        aspect-ratio: 1 / 1;
        font-size: 2.4rem;
        font-weight: 800;
        border-radius: 14px;
        border: 2px solid #cbd5e1;
        background: #f8fafc;
        transition: transform 0.12s ease, background 0.12s ease;
    }
    .cell-btn:not(:disabled):hover {
        background: #e2e8f0;
        transform: scale(1.03);
    }
    .cell-x { color: #0d6efd; }
    .cell-o { color: #dc3545; }
</style>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card main-card bg-white p-4 p-md-5">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h4 class="fw-bold text-dark mb-0">#{{ $game->id }} · {{ $game->title }}</h4>
                    <small class="text-muted">
                        Tú eres: <strong>{{ auth()->user()->name }}</strong>
                        @if ($mySymbol)
                            · Símbolo: <span class="badge bg-dark">{{ $mySymbol }}</span>
                        @endif
                    </small>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('games.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
                    @if ($mySymbol && in_array($game->status, ['esperando', 'en_curso'], true))
                        <form method="POST" action="{{ route('games.abandon', $game) }}"
                              onsubmit="return confirm('¿Seguro que deseas abandonar esta partida?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">Abandonar</button>
                        </form>
                    @endif
                </div>
            </div>

            <x-alert-messages />

            <div class="bg-light rounded-3 p-3 mb-4 small">
                <div class="row text-center g-2">
                    <div class="col-6">
                        <div class="text-muted">Jugador X</div>
                        <div class="fw-bold">{{ $game->playerX?->name }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Jugador O</div>
                        <div class="fw-bold" id="player-o-label">{{ $game->playerO?->name ?? 'Esperando oponente...' }}</div>
                    </div>
                </div>
                <hr class="my-2">
                <div class="text-center" id="game-status-banner">
                    @if ($game->status === 'esperando')
                        <span class="badge bg-warning text-dark">ESPERANDO SEGUNDO JUGADOR (O)</span>
                    @elseif ($game->status === 'en_curso')
                        Estado: <span class="badge bg-primary">EN CURSO</span> ·
                        Turno actual: <strong id="turn-name">{{ $game->currentTurnUser?->name }}</strong>
                        (<span id="turn-symbol">{{ $game->current_symbol }}</span>)
                    @elseif ($game->status === 'ganado')
                        <span class="badge bg-success">GANADOR: {{ $game->winner?->name }} ({{ $game->winning_symbol }})</span>
                    @elseif ($game->status === 'empate')
                        <span class="badge bg-secondary">EMPATE</span>
                    @else
                        <span class="badge bg-danger">{{ strtoupper($game->status) }}</span>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('games.move', $game) }}" id="move-form">
                @csrf
                <input type="hidden" name="cell" id="cell-input">

                <div class="board-grid" id="board-grid">
                    @for ($i = 0; $i < 9; $i++)
                        @php
                            $val = $game->board[$i] ?? null;
                            $canClick = $game->status === 'en_curso'
                                && $mySymbol !== null
                                && (int) $game->current_turn_user_id === (int) auth()->id()
                                && $val === null;
                        @endphp
                        <button type="button"
                                data-cell="{{ $i }}"
                                class="cell-btn {{ $val === 'X' ? 'cell-x' : ($val === 'O' ? 'cell-o' : '') }}"
                                @disabled(! $canClick)>
                            {{ $val ?? '' }}
                        </button>
                    @endfor
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const stateUrl = @json(route('games.state', $game));
    const currentUserId = @json((int) auth()->id());
    const mySymbol = @json($mySymbol);
    const boardGrid = document.getElementById('board-grid');
    const moveForm = document.getElementById('move-form');
    const cellInput = document.getElementById('cell-input');
    const banner = document.getElementById('game-status-banner');
    const playerOLabel = document.getElementById('player-o-label');

    boardGrid.addEventListener('click', (e) => {
        const btn = e.target.closest('.cell-btn');
        if (!btn || btn.disabled) return;
        cellInput.value = btn.dataset.cell;
        moveForm.submit();
    });

    function renderState(data) {
        playerOLabel.textContent = data.player_o_name || 'Esperando oponente...';

        const buttons = boardGrid.querySelectorAll('.cell-btn');
        buttons.forEach((btn, idx) => {
            const val = data.board[idx] ?? null;
            btn.textContent = val || '';
            btn.classList.remove('cell-x', 'cell-o');
            if (val === 'X') btn.classList.add('cell-x');
            if (val === 'O') btn.classList.add('cell-o');

            const isMyTurn = data.status === 'en_curso'
                && mySymbol !== null
                && Number(data.current_turn_user_id) === currentUserId
                && val === null;
            btn.disabled = !isMyTurn;
        });

        if (data.status === 'esperando') {
            banner.innerHTML = '<span class="badge bg-warning text-dark">ESPERANDO SEGUNDO JUGADOR (O)</span>';
        } else if (data.status === 'en_curso') {
            const turnTag = data.is_my_turn
                ? '<span class="badge bg-success ms-1">¡ES TU TURNO!</span>'
                : '<span class="badge bg-secondary ms-1">Turno del oponente</span>';
            banner.innerHTML = `Estado: <span class="badge bg-primary">EN CURSO</span> · Turno de: <strong>${data.current_turn_name}</strong> (${data.current_symbol}) ${turnTag}`;
        } else if (data.status === 'ganado') {
            banner.innerHTML = `<span class="badge bg-success">GANADOR: ${data.winner_name || ''} (${data.winning_symbol || ''})</span>`;
        } else if (data.status === 'empate') {
            banner.innerHTML = '<span class="badge bg-secondary">PARTIDA TERMINADA EN EMPATE</span>';
        } else {
            banner.innerHTML = `<span class="badge bg-danger">${String(data.status).toUpperCase()}</span>`;
        }
    }

    setInterval(async () => {
        try {
            const res = await fetch(stateUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const data = await res.json();
            renderState(data);
        } catch (_) {}
    }, 2000);
})();
</script>
@endpush