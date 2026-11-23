<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/registro', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/registro', [AuthController::class, 'register'])->name('register.submit');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Rutas para usuarios autenticados (Jugador y Administrador)
Route::middleware(['auth', 'role:jugador,administrador'])->group(function () {
    Route::get('/partidas', [GameController::class, 'index'])->name('games.index');
    Route::get('/historial', [GameController::class, 'history'])->name('games.history');
    Route::post('/partidas', [GameController::class, 'store'])->name('games.store');
    Route::post('/partidas/{game}/unirse', [GameController::class, 'join'])->name('games.join');
    Route::get('/partidas/{game}', [GameController::class, 'show'])->name('games.show');
    Route::get('/partidas/{game}/estado', [GameController::class, 'state'])->name('games.state');
    Route::post('/partidas/{game}/movimiento', [GameController::class, 'move'])->name('games.move');
    Route::post('/partidas/{game}/abandonar', [GameController::class, 'abandon'])->name('games.abandon');
    Route::post('/partidas/{game}/cancelar', [GameController::class, 'cancel'])->name('games.cancel');
});

// Administración de usuarios: solo Administrador
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/usuarios', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/usuarios/crear', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/usuarios', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/usuarios/{user}/editar', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/usuarios/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/usuarios/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});