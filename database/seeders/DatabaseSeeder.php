<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gato.local'],
            [
                'name' => 'Administrador del Sistema',
                'password' => Hash::make('Admin1234!'),
                'role' => 'administrador',
            ]
        );

        $jugador1 = User::updateOrCreate(
            ['email' => 'jugador1@gato.local'],
            [
                'name' => 'Jugador Uno (X)',
                'password' => Hash::make('Jugador1234!'),
                'role' => 'jugador',
            ]
        );

        User::updateOrCreate(
            ['email' => 'jugador2@gato.local'],
            [
                'name' => 'Jugador Dos (O)',
                'password' => Hash::make('Jugador1234!'),
                'role' => 'jugador',
            ]
        );

        Game::firstOrCreate(
            ['title' => 'Partida Inicial de Prueba'],
            [
                'player_x_id' => $jugador1->id,
                'player_o_id' => null,
                'current_turn_user_id' => $jugador1->id,
                'current_symbol' => 'X',
                'board' => array_fill(0, 9, null),
                'status' => 'esperando',
                'winner_id' => null,
                'winning_symbol' => null,
            ]
        );
    }
}