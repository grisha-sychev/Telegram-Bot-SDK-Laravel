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
        'first_name',
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
     * Получить класс бота
     */
    public function getBotClass(): string
    {
        return 'App\\Bots\\' . ucfirst($this->name) . 'Bot';
    }

    /**
     * Проверить существование класса бота
     */
    public function botClassExists(): bool
    {
        return class_exists($this->getBotClass());
    }

    /**
     * Получить маскированный токен для отображения
     */
    public function getMaskedTokenAttribute(): string
    {
        return substr($this->token, 0, 10) . '...';
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
} 