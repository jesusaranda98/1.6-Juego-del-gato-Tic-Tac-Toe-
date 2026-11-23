<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('player_x_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('player_o_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('current_turn_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('current_symbol', 1)->default('X');
            $table->json('board');
            $table->enum('status', ['esperando', 'en_curso', 'ganado', 'empate', 'abandonada', 'cancelada'])->default('esperando');
            $table->foreignId('winner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('winning_symbol', 1)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};