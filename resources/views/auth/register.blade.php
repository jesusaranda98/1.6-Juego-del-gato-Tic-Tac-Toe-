@extends('layouts.app')

@section('title', 'Registro · Juego del Gato')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 85vh;">
    <div class="col-md-6 col-lg-5">
        <div class="card main-card bg-white p-4 p-md-5">
            <div class="text-center mb-4">
                <h3 class="fw-bold text-dark mb-1">Crear Cuenta de Jugador</h3>
                <p class="text-muted small mb-0">Regístrate para crear partidas o unirte a una</p>
            </div>

            <x-alert-messages />

            <form method="POST" action="{{ route('register.submit') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Nombre</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           class="form-control" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Contraseña (mín. 8 caracteres)</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-semibold">Confirmar contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-semibold mb-3">
                    Registrarme
                </button>

                <div class="text-center small">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">Iniciar sesión</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection