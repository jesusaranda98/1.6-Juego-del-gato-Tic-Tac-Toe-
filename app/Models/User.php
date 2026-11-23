<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'administrador';
    }

    public function isJugador(): bool
    {
        return $this->role === 'jugador';
    }

    public function gamesAsX(): HasMany
    {
        return $this->hasMany(Game::class, 'player_x_id');
    }

    public function gamesAsO(): HasMany
    {
        return $this->hasMany(Game::class, 'player_o_id');
    }
}