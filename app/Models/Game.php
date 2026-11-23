<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'player_x_id',
        'player_o_id',
        'current_turn_user_id',
        'current_symbol',
        'board',
        'status',
        'winner_id',
        'winning_symbol',
    ];

    protected function casts(): array
    {
        return [
            'board' => 'array',
        ];
    }

    public function playerX(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_x_id');
    }

    public function playerO(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_o_id');
    }

    public function currentTurnUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_turn_user_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function symbolForUser(int $userId): ?string
    {
        if ((int) $this->player_x_id === $userId) {
            return 'X';
        }
        if ((int) $this->player_o_id === $userId) {
            return 'O';
        }

        return null;
    }

    public function isParticipant(int $userId): bool
    {
        return $this->symbolForUser($userId) !== null;
    }
}