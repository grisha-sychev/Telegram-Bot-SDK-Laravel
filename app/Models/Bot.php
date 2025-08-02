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
        'dev_token',
        'prod_token',
        'dev_domain',
        'prod_domain',
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
        'dev_token',
        'prod_token',
        'webhook_secret',
    ];

    /**
     * Статическое свойство для хранения текущего окружения
     */
    private static ?string $currentEnvironment = null;

    /**
     * Получить текущее окружение из env или статического свойства
     */
    public static function getCurrentEnvironment(): string
    {
        if (self::$currentEnvironment !== null) {
            return self::$currentEnvironment;
        }
        return env('APP_ENV', 'dev');
    }

    /**
     * Установить текущее окружение
     */
    public static function setCurrentEnvironment(string $environment): void
    {
        self::$currentEnvironment = $environment;
    }

    /**
     * Сбросить текущее окружение к значению из env
     */
    public static function resetCurrentEnvironment(): void
    {
        self::$currentEnvironment = null;
    }

    /**
     * Получить токен для текущего окружения
     * ВАЖНО: Этот метод используется только для совместимости
     * Для webhook используйте getTokenForEnvironment() с конкретным окружением
     */
    public function getTokenAttribute(): ?string
    {
        $environment = self::getCurrentEnvironment();
        return $this->getTokenForEnvironment($environment);
    }

    /**
     * Получить токен для указанного окружения
     */
    public function getTokenForEnvironment(string $environment): ?string
    {
        return $environment === 'prod' ? $this->prod_token : $this->dev_token;
    }

    /**
     * Установить токен для указанного окружения
     */
    public function setTokenForEnvironment(string $environment, string $token): void
    {
        if ($environment === 'prod') {
            $this->prod_token = $token;
        } else {
            $this->dev_token = $token;
        }
    }

    /**
     * Проверить наличие токена для указанного окружения
     */
    public function hasTokenForEnvironment(string $environment): bool
    {
        $token = $this->getTokenForEnvironment($environment);
        return !empty($token);
    }

    /**
     * Получить домен для текущего окружения
     */
    public function getDomainAttribute(): ?string
    {
        $environment = self::getCurrentEnvironment();
        return $this->getDomainForEnvironment($environment);
    }

    /**
     * Получить домен для указанного окружения
     */
    public function getDomainForEnvironment(string $environment): ?string
    {
        return $environment === 'prod' ? $this->prod_domain : $this->dev_domain;
    }

    /**
     * Установить домен для указанного окружения
     */
    public function setDomainForEnvironment(string $environment, string $domain): void
    {
        if ($environment === 'prod') {
            $this->prod_domain = $domain;
        } else {
            $this->dev_domain = $domain;
        }
    }

    /**
     * Проверить наличие домена для указанного окружения
     */
    public function hasDomainForEnvironment(string $environment): bool
    {
        $domain = $this->getDomainForEnvironment($environment);
        return !empty($domain);
    }

    /**
     * Получить маскированный токен для отображения
     */
    public function getMaskedTokenAttribute(): string
    {
        $token = $this->getTokenAttribute();
        return $token ? substr($token, 0, 10) . '...' : 'Не установлен';
    }

    /**
     * Получить маскированный токен для указанного окружения
     */
    public function getMaskedTokenForEnvironment(string $environment): string
    {
        $token = $this->getTokenForEnvironment($environment);
        return $token ? substr($token, 0, 10) . '...' : 'Не установлен';
    }

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
     * Активные боты
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Поиск бота по токену (для обратной совместимости)
     */
    public function scopeByToken($query, string $token)
    {
        return $query->where(function($q) use ($token) {
            $q->where('dev_token', $token)->orWhere('prod_token', $token);
        });
    }

    /**
     * Поиск бота по имени
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }

    /**
     * Боты с токенами для указанного окружения
     */
    public function scopeWithTokenForEnvironment($query, string $environment)
    {
        $field = $environment === 'prod' ? 'prod_token' : 'dev_token';
        return $query->whereNotNull($field)->where($field, '!=', '');
    }

    /**
     * Боты с доменами для указанного окружения
     */
    public function scopeWithDomainForEnvironment($query, string $environment)
    {
        $field = $environment === 'prod' ? 'prod_domain' : 'dev_domain';
        return $query->whereNotNull($field)->where($field, '!=', '');
    }

    /**
     * Получить полный webhook URL с доменом текущего окружения
     */
    public function getFullWebhookUrl(): ?string
    {
        $environment = self::getCurrentEnvironment();
        $domain = $this->getDomainForEnvironment($environment);
        
        if (!$domain || !$this->webhook_url) {
            return null;
        }
        
        return rtrim($domain, '/') . $this->webhook_url;
    }

    /**
     * Получить полный webhook URL для указанного окружения
     */
    public function getFullWebhookUrlForEnvironment(string $environment): ?string
    {
        $domain = $this->getDomainForEnvironment($environment);
        
        if (!$domain || !$this->webhook_url) {
            return null;
        }
        
        return rtrim($domain, '/') . $this->webhook_url;
    }

    /**
     * Проверить изоляцию бота - что он работает только в своем окружении
     */
    public function isIsolatedForEnvironment(string $environment): bool
    {
        // Проверяем, что у бота есть токен для этого окружения
        if (!$this->hasTokenForEnvironment($environment)) {
            return false;
        }
        
        // Проверяем, что у бота есть домен для этого окружения
        if (!$this->hasDomainForEnvironment($environment)) {
            return false;
        }
        
        return true;
    }

    /**
     * Получить список ботов для конкретного окружения
     */
    public static function getBotsForEnvironment(string $environment): \Illuminate\Database\Eloquent\Collection
    {
        return self::enabled()
            ->withTokenForEnvironment($environment)
            ->withDomainForEnvironment($environment)
            ->get();
    }
    
    /**
     * Проверить, что webhook URL изолирован для указанного окружения
     */
    public function isWebhookIsolatedForEnvironment(string $environment): bool
    {
        if (!$this->webhook_url) {
            return false;
        }
        
        $domain = $this->getDomainForEnvironment($environment);
        if (!$domain) {
            return false;
        }
        
        // Проверяем, что webhook URL уникален для этого окружения
        $fullWebhookUrl = $this->getFullWebhookUrlForEnvironment($environment);
        
        // Ищем другие боты с таким же webhook URL
        $conflictingBots = self::enabled()
            ->where('id', '!=', $this->id)
            ->where('webhook_url', $this->webhook_url)
            ->get();
        
        foreach ($conflictingBots as $otherBot) {
            // Если домены одинаковые - проверяем изоляцию по токенам
            if ($this->dev_domain === $otherBot->dev_domain && 
                $this->prod_domain === $otherBot->prod_domain) {
                
                // Если у ботов разные токены для разных окружений - это нормально
                if ($this->hasTokenForEnvironment($environment) && 
                    $otherBot->hasTokenForEnvironment($environment === 'dev' ? 'prod' : 'dev')) {
                    continue; // Боты изолированы по токенам
                }
                
                // Если у обоих ботов есть токены для одного окружения - это конфликт
                if ($this->hasTokenForEnvironment($environment) && 
                    $otherBot->hasTokenForEnvironment($environment)) {
                    return false; // Конфликт токенов
                }
            }
            
            // Если домены разные - проверяем по доменам
            if ($otherBot->getDomainForEnvironment($environment) === $domain) {
                return false;
            }
            
            // Дополнительная проверка: если у другого бота есть токен для другого окружения
            // и домены разные, то это нормально (изоляция по доменам)
            $otherEnvironment = $environment === 'dev' ? 'prod' : 'dev';
            if ($otherBot->hasTokenForEnvironment($otherEnvironment) && 
                $otherBot->hasDomainForEnvironment($otherEnvironment)) {
                
                $otherDomain = $otherBot->getDomainForEnvironment($otherEnvironment);
                if ($otherDomain && $otherDomain !== $domain) {
                    // Боты изолированы по доменам - это нормально
                    continue;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Получить список конфликтующих ботов для webhook URL
     */
    public function getConflictingBots(): \Illuminate\Database\Eloquent\Collection
    {
        if (!$this->webhook_url) {
            return collect();
        }
        
        return self::enabled()
            ->where('id', '!=', $this->id)
            ->where('webhook_url', $this->webhook_url)
            ->get();
    }
    
    /**
     * Проверить, что бот полностью изолирован (токен + домен + webhook)
     */
    public function isFullyIsolatedForEnvironment(string $environment): bool
    {
        return $this->isIsolatedForEnvironment($environment) && 
               $this->isWebhookIsolatedForEnvironment($environment);
    }
    
    /**
     * Получить детальную информацию об изоляции для окружения
     */
    public function getIsolationDetailsForEnvironment(string $environment): array
    {
        return [
            'has_token' => $this->hasTokenForEnvironment($environment),
            'has_domain' => $this->hasDomainForEnvironment($environment),
            'has_webhook' => !empty($this->webhook_url),
            'webhook_isolated' => $this->isWebhookIsolatedForEnvironment($environment),
            'fully_isolated' => $this->isFullyIsolatedForEnvironment($environment),
            'token' => $this->getMaskedTokenForEnvironment($environment),
            'domain' => $this->getDomainForEnvironment($environment),
            'webhook_url' => $this->getFullWebhookUrlForEnvironment($environment),
            'conflicting_bots' => $this->getConflictingBots()->pluck('name')->toArray()
        ];
    }
} 