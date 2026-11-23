@extends('layouts.app')

@section('title', 'Editar Usuario #' . $user->id)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card main-card bg-white p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-dark mb-0">Editar Usuario #{{ $user->id }}</h4>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
            </div>

            <x-alert-messages />

            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Rol</label>
                    <select name="role" class="form-select" required>
                        <option value="jugador" @selected(old('role', $user->role) === 'jugador')>Jugador</option>
                        <option value="administrador" @selected(old('role', $user->role) === 'administrador')>Administrador</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Nueva contraseña (opcional)</label>
                    <input type="password" name="password" class="form-control" placeholder="Dejar vacío para conservar">
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-semibold">Actualizar usuario</button>
            </form>
        </div>
    </div>
</div>
@endsection