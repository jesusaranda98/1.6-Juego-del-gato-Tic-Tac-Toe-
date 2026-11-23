@extends('layouts.app')

@section('title', 'Partidas · Juego del Gato')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card main-card bg-white p-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h4 class="fw-bold text-dark mb-0">Crear Nueva Partida</h4>
                    <small class="text-muted">Sesión activa: <code>{{ \Illuminate\Support\Str::limit($sessionId, 14) }}</code></small>
                </div>
            </div>

            <x-alert-messages />

            <form method="POST" action="{{ route('games.store') }}" class="row g-2">
                @csrf
                <div class="col-sm-8">
                    <input type="text" name="title" value="{{ old('title') }}"
                           class="form-control" placeholder="Título opcional (ej. Reto del Lab)">
                </div>
                <div class="col-sm-4">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        + Crear partida (X)
                    </button>
                </div>
            </form>
        </div>

        <div class="card main-card bg-white p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3">Partidas Disponibles para Unirse (O)</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Partida</th>
                            <th>Creador (X)</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($availableGames as $game)
                            <tr>
                                <td class="text-muted">#{{ $game->id }}</td>
                                <td class="fw-semibold">{{ $game->title }}</td>
                                <td>{{ $game->playerX?->name }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('games.join', $game) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success fw-semibold">
                                            Unirse como O
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">
                                    No hay partidas de otros jugadores esperando oponente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card main-card bg-white p-4">
            <h5 class="fw-bold text-dark mb-3">Mis Partidas Activas / En Espera</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Partida</th>
                            <th>Símbolo</th>
                            <th>Oponente</th>
                            <th>Estado</th>
                            <th class="text-end">Ir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($myActiveGames as $game)
                            @php $sym = $game->symbolForUser(auth()->id()); @endphp
                            <tr>
                                <td class="fw-semibold">#{{ $game->id }} · {{ $game->title }}</td>
                                <td><span class="badge bg-dark">{{ $sym }}</span></td>
                                <td>
                                    @if ($sym === 'X')
                                        {{ $game->playerO?->name ?? 'Esperando jugador O...' }}
                                    @else
                                        {{ $game->playerX?->name }}
                                    @endif
                                </td>
                                <td><span class="badge bg-primary">{{ str_replace('_', ' ', strtoupper($game->status)) }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('games.show', $game) }}" class="btn btn-sm btn-outline-primary">
                                        Abrir tablero
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">
                                    No tienes partidas activas en este momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card main-card bg-white p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3">Jugadores Registrados ({{ $players->count() }})</h5>
            <ul class="list-group list-group-flush small">
                @forelse ($players as $player)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <div class="fw-semibold">{{ $player->name }}</div>
                            <div class="text-muted">{{ $player->email }}</div>
                        </div>
                        @if ((int) $player->id === (int) auth()->id())
                            <span class="badge bg-success">Tú</span>
                        @else
                            <span class="badge bg-light text-dark border">Jugador</span>
                        @endif
                    </li>
                @empty
                    <li class="list-group-item text-muted px-0">Sin jugadores registrados.</li>
                @endforelse
            </ul>
        </div>

        @if ($isAdmin)
            <div class="card main-card bg-white p-4">
                <h5 class="fw-bold text-dark mb-3">Partidas en Curso / Espera (Admin)</h5>
                @forelse ($allActiveGames as $ag)
                    <div class="border rounded-3 p-2 mb-2 d-flex justify-content-between align-items-center small">
                        <div>
                            <div class="fw-semibold">#{{ $ag->id }} · {{ $ag->title }}</div>
                            <div class="text-muted">{{ $ag->playerX?->name }} vs {{ $ag->playerO?->name ?? '—' }}</div>
                        </div>
                        <form method="POST" action="{{ route('games.cancel', $ag) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">Cancelar</button>
                        </form>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No hay partidas activas.</p>
                @endforelse
            </div>
        @endif
    </div>
</div>
@endsection