<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'tg_id',
        'clue',
        'payload',
    ];

    protected $casts = [
        'tg_id' => 'integer',
        'payload' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user telegram that owns the message
     */
    public function userTelegram(): BelongsTo
    {
        return $this->belongsTo(UserTelegram::class, 'tg_id', 'telegram_id');
    }

    /**
     * Find latest message for a telegram user
     */
    public static function getLatestForUser(int $telegramId): ?self
    {
        return self::where('tg_id', $telegramId)
            ->latest()
            ->first();
    }

    /**
     * Set clue for user
     */
    public static function setClueForUser(int $telegramId, string $clue, array $payload = null): self
    {
        return self::updateOrCreate(
            ['tg_id' => $telegramId],
            [
                'clue' => $clue,
                'payload' => $payload,
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Get clue for user
     */
    public static function getClueForUser(int $telegramId): ?string
    {
        $message = self::getLatestForUser($telegramId);
        return $message?->clue;
    }

    /**
     * Get payload for user
     */
    public static function getPayloadForUser(int $telegramId): ?array
    {
        $message = self::getLatestForUser($telegramId);
        return $message?->payload;
    }

    /**
     * Clear state for user
     */
    public static function clearStateForUser(int $telegramId): bool
    {
        return self::where('tg_id', $telegramId)->delete();
    }

    /**
     * Clear only payload for user
     */
    public static function clearPayloadForUser(int $telegramId): bool
    {
        return self::where('tg_id', $telegramId)->update(['payload' => null]);
    }

    /**
     * Clear only clue for user
     */
    public static function clearClueForUser(int $telegramId): bool
    {
        return self::where('tg_id', $telegramId)->update(['clue' => null]);
    }

    /**
     * Check if user has specific clue
     */
    public static function userHasClue(int $telegramId, string $clue): bool
    {
        return self::where('tg_id', $telegramId)
            ->where('clue', $clue)
            ->exists();
    }

    /**
     * Check if user has any clue from array
     */
    public static function userHasAnyClue(int $telegramId, array $clues): bool
    {
        return self::where('tg_id', $telegramId)
            ->whereIn('clue', $clues)
            ->exists();
    }

    /**
     * Get messages created today
     */
    public static function getTodayCount(): int
    {
        return self::whereDate('created_at', Carbon::today())->count();
    }

    /**
     * Get active conversations (messages in last N minutes)
     */
    public static function getActiveConversations(int $minutes = 30): int
    {
        return self::where('updated_at', '>=', Carbon::now()->subMinutes($minutes))
            ->distinct('tg_id')
            ->count('tg_id');
    }

    /**
     * Clean old messages (older than N days)
     */
    public static function cleanOldMessages(int $days = 30): int
    {
        return self::where('updated_at', '<', Carbon::now()->subDays($days))->delete();
    }
}
