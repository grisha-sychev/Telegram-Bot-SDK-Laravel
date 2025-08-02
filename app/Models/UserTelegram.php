<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class UserTelegram extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'telegram_id',
        'is_bot',
        'first_name',
        'last_name',
        'username',
        'language_code',
        'is_premium',
    ];

    protected $casts = [
        'telegram_id' => 'integer',
        'is_bot' => 'boolean',
        'is_premium' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get full name of the user
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get display name (username or full name)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->username ? '@' . $this->username : $this->full_name;
    }

    /**
     * Check if user is premium
     */
    public function isPremium(): bool
    {
        return $this->is_premium;
    }

    /**
     * Update user data from Telegram API response
     */
    public function updateFromTelegramData(array $data): bool
    {
        return $this->update([
            'first_name' => $data['first_name'] ?? $this->first_name,
            'last_name' => $data['last_name'] ?? $this->last_name,
            'username' => $data['username'] ?? $this->username,
            'language_code' => $data['language_code'] ?? $this->language_code,
            'is_premium' => $data['is_premium'] ?? $this->is_premium,
        ]);
    }

    /**
     * Find user by telegram ID
     */
    public static function findByTelegramId(int $telegramId): ?self
    {
        return self::where('telegram_id', $telegramId)->first();
    }

    /**
     * Create or update user from Telegram data
     */
    public static function createOrUpdateFromTelegram(array $data): self
    {
        return self::updateOrCreate(
            ['telegram_id' => $data['id']],
            [
                'is_bot' => $data['is_bot'] ?? false,
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? null,
                'username' => $data['username'] ?? null,
                'language_code' => $data['language_code'] ?? 'en',
                'is_premium' => $data['is_premium'] ?? false,
            ]
        );
    }

    /**
     * Get users created today
     */
    public static function getNewUsersToday(): int
    {
        return self::whereDate('created_at', Carbon::today())->count();
    }

    /**
     * Get active users (users who were active in last N days)
     * Note: Since we removed Message model, this now counts users who were created in last N days
     */
    public static function getActiveUsers(int $days = 7): int
    {
        return self::where('created_at', '>=', Carbon::now()->subDays($days))->count();
    }
}