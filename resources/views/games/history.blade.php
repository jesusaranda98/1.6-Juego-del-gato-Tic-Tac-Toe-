@extends('layouts.app')

@section('title', 'Historial de Partidas')

@section('content')
<div class="card main-card bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                {{ $isAdmin ? 'Historial General de Partidas' : 'Mi Historial de Partidas' }}
            </h3>
            <p class="text-muted small mb-0">Persistido en base de datos para {{ auth()->user()->name }}</p>
        </div>
        <a href="{{ route('games.index') }}" class="btn btn-outline-primary btn-sm fw-semibold">Volver a partidas</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Partida</th>
                    <th>Jugador X</th>
                    <th>Jugador O</th>
                    <th>Estado</th>
                    <th>Ganador</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($games as $g)
                    <tr>
                        <td class="text-muted">#{{ $g->id }}</td>
                        <td class="fw-semibold">{{ $g->title }}</td>
                        <td>{{ $g->playerX?->name }}</td>
                        <td>{{ $g->playerO?->name ?? '—' }}</td>
                        <td><span class="badge bg-dark">{{ str_replace('_', ' ', strtoupper($g->status)) }}</span></td>
                        <td>{{ $g->winner?->name ?? ($g->status === 'empate' ? 'Empate' : '—') }}</td>
                        <td>
                            <a href="{{ route('games.show', $g) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No hay partidas en el historial.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection