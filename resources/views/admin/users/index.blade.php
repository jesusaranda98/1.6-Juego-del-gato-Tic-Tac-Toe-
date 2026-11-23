@extends('layouts.app')

@section('title', 'Usuarios · Administración')

@section('content')
<div class="card main-card bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0">Gestión de Usuarios</h3>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm fw-semibold">+ Nuevo usuario</a>
    </div>

    <x-alert-messages />

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $u)
                    <tr>
                        <td class="text-muted">#{{ $u->id }}</td>
                        <td class="fw-semibold">{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>
                            <span class="badge {{ $u->role === 'administrador' ? 'bg-warning text-dark' : 'bg-info text-dark' }}">
                                {{ strtoupper($u->role) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                      onsubmit="return confirm('¿Eliminar al usuario {{ $u->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection