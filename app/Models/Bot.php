<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bot extends Model
{
    use HasFactory;

    protected $table = 'bots';

    protected $fillable = [
        'name',
        'token',
        'username',
        'display_name',
        'description',
        'bot_id',
        'enabled',
        'webhook_url',
        'webhook_secret',
        'settings',
        'admin_ids',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'settings' => 'array',
        'admin_ids' => 'array',
    ];

    protected $hidden = [
        'token',
        'webhook_secret',
    ];

    /**
     * Получить токен бота
     */
    public function getTokenAttribute(): ?string
    {
        return $this->attributes['token'] ?? null;
    }

    /**
     * Установить токен бота
     */
    public function setTokenAttribute(string $token): void
    {
        $this->attributes['token'] = $token;
    }

    /**
     * Проверить наличие токена
     */
    public function hasToken(): bool
    {
        return !empty($this->token);
    }

    /**
     * Получить маскированный токен для отображения
     */
    public function getMaskedTokenAttribute(): string
    {
        $token = $this->token;
        return $token ? substr($token, 0, 10) . '...' : 'Не установлен';
    }

    /**
     * Получить класс бота
     */
    public function getBotClass()
    {
        return 'App\\Bots\\' . str_replace(' ', '', $this->name) . 'Bot';
    }

    /**
     * Проверить существование класса бота
     */
    public function botClassExists(): bool
    {
        return class_exists($this->getBotClass());
    }

    /**
     * Активные боты
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Поиск бота по токену
     */
    public function scopeByToken($query, string $token)
    {
        return $query->where('token', $token);
    }

    /**
     * Поиск бота по имени
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }

    /**
     * Боты с токенами
     */
    public function scopeWithToken($query)
    {
        return $query->whereNotNull('token')->where('token', '!=', '');
    }

    /**
     * Боты с webhook URL
     */
    public function scopeWithWebhookUrl($query)
    {
        return $query->whereNotNull('webhook_url')->where('webhook_url', '!=', '');
    }

    /**
     * Получить webhook URL
     */
    public function getWebhookUrl(): ?string
    {
        return $this->webhook_url;
    }

    /**
     * Установить webhook URL
     */
    public function setWebhookUrl(string $url): void
    {
        $this->webhook_url = $url;
    }

    /**
     * Проверить наличие webhook URL
     */
    public function hasWebhookUrl(): bool
    {
        return !empty($this->webhook_url);
    }

    /**
     * Получить полный webhook URL
     */
    public function getFullWebhookUrl(): ?string
    {
        if (!$this->webhook_url) {
            return null;
        }
        
        return $this->webhook_url;
    }

    /**
     * Получить список активных ботов с токенами
     */
    public static function getActiveBots(): \Illuminate\Database\Eloquent\Collection
    {
        return self::enabled()->withToken()->get();
    }
} 