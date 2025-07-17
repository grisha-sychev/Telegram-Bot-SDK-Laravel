# ⚙️ Конфигурация TegBot

## Обзор

TegBot предоставляет гибкую систему конфигурации для настройки всех аспектов работы бота:

- 🔧 **Основные настройки**: Токен, webhook, базовые параметры
- 🛡️ **Безопасность**: Защита, аутентификация, ограничения
- 📁 **Файлы**: Загрузка, хранение, обработка медиа
- 📊 **Логирование**: Детальная настройка логов
- 🚀 **Производительность**: Кэширование, очереди, оптимизация
- 📱 **API**: Настройки взаимодействия с Telegram

## Основной файл конфигурации

### config/tegbot.php

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Основные настройки бота
    |--------------------------------------------------------------------------
    */
    'token' => env('TEGBOT_TOKEN'),
    'debug' => env('TEGBOT_DEBUG', false),
    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | API настройки
    |--------------------------------------------------------------------------
    */
    'api' => [
        'base_url' => env('TEGBOT_API_URL', 'https://api.telegram.org'),
        'timeout' => env('TEGBOT_API_TIMEOUT', 30),
        'retries' => env('TEGBOT_API_RETRIES', 3),
        'retry_delay' => env('TEGBOT_API_RETRY_DELAY', 1),
        'rate_limit_delay' => env('TEGBOT_API_RATE_LIMIT_DELAY', 5),
        'user_agent' => env('TEGBOT_USER_AGENT', 'TegBot/2.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Безопасность
    |--------------------------------------------------------------------------
    */
    'security' => [
        'webhook_secret' => env('TEGBOT_WEBHOOK_SECRET'),
        'admin_ids' => array_filter(explode(',', env('TEGBOT_ADMIN_IDS', ''))),
        'allowed_ips' => array_filter(explode(',', env('TEGBOT_ALLOWED_IPS', ''))),
        
        'spam_protection' => [
            'enabled' => env('TEGBOT_SPAM_PROTECTION', true),
            'max_messages_per_minute' => env('TEGBOT_SPAM_LIMIT', 20),
            'ban_duration_minutes' => env('TEGBOT_SPAM_BAN_DURATION', 60),
            'whitelist_admins' => true,
        ],
        
        'rate_limits' => [
            'global' => env('TEGBOT_RATE_LIMIT_GLOBAL', 100),
            'per_user' => env('TEGBOT_RATE_LIMIT_USER', 20),
            'per_chat' => env('TEGBOT_RATE_LIMIT_CHAT', 50),
            'commands' => env('TEGBOT_RATE_LIMIT_COMMANDS', 10),
        ],
        
        'validation' => [
            'max_message_length' => env('TEGBOT_MAX_MESSAGE_LENGTH', 4000),
            'block_html' => env('TEGBOT_BLOCK_HTML', true),
            'filter_sql_injection' => env('TEGBOT_FILTER_SQL', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Файлы и медиа
    |--------------------------------------------------------------------------
    */
    'files' => [
        'download_path' => env('TEGBOT_DOWNLOAD_PATH', storage_path('app/tegbot/downloads')),
        'temp_path' => env('TEGBOT_TEMP_PATH', storage_path('app/tegbot/temp')),
        'max_file_size' => env('TEGBOT_MAX_FILE_SIZE', 20 * 1024 * 1024), // 20MB
        'allowed_types' => explode(',', env('TEGBOT_ALLOWED_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx,txt')),
        'auto_cleanup' => env('TEGBOT_AUTO_CLEANUP', true),
        'cleanup_hours' => env('TEGBOT_CLEANUP_HOURS', 24),
        
        'thumbnails' => [
            'enabled' => env('TEGBOT_THUMBNAILS', true),
            'quality' => env('TEGBOT_THUMBNAIL_QUALITY', 80),
            'max_width' => env('TEGBOT_THUMBNAIL_WIDTH', 300),
            'max_height' => env('TEGBOT_THUMBNAIL_HEIGHT', 300),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Логирование
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('TEGBOT_LOGGING', true),
        'level' => env('TEGBOT_LOG_LEVEL', 'info'),
        'channels' => [
            'default' => env('TEGBOT_LOG_CHANNEL', 'stack'),
            'errors' => env('TEGBOT_ERROR_CHANNEL', 'daily'),
            'activity' => env('TEGBOT_ACTIVITY_CHANNEL', 'tegbot_activity'),
            'security' => env('TEGBOT_SECURITY_CHANNEL', 'tegbot_security'),
        ],
        'max_entries' => env('TEGBOT_LOG_MAX_ENTRIES', 10000),
        'retention_days' => env('TEGBOT_LOG_RETENTION', 30),
        'structured_logs' => env('TEGBOT_STRUCTURED_LOGS', true),
        'sensitive_fields' => ['password', 'token', 'secret', 'key'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Кэширование
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('TEGBOT_CACHE', true),
        'driver' => env('TEGBOT_CACHE_DRIVER', 'redis'),
        'prefix' => env('TEGBOT_CACHE_PREFIX', 'tegbot'),
        'ttl' => env('TEGBOT_CACHE_TTL', 3600),
        'user_data_ttl' => env('TEGBOT_USER_CACHE_TTL', 1800),
        'command_cache_ttl' => env('TEGBOT_COMMAND_CACHE_TTL', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Очереди
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'enabled' => env('TEGBOT_QUEUE', false),
        'connection' => env('TEGBOT_QUEUE_CONNECTION', 'redis'),
        'queue' => env('TEGBOT_QUEUE_NAME', 'tegbot'),
        'retry_after' => env('TEGBOT_QUEUE_RETRY', 90),
        'timeout' => env('TEGBOT_QUEUE_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Мониторинг
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'health_checks' => [
            'enabled' => env('TEGBOT_HEALTH_CHECKS', true),
            'interval_minutes' => env('TEGBOT_HEALTH_INTERVAL', 5),
            'timeout' => env('TEGBOT_HEALTH_TIMEOUT', 10),
        ],
        
        'alerts' => [
            'enabled' => env('TEGBOT_ALERTS', true),
            'channels' => explode(',', env('TEGBOT_ALERT_CHANNELS', 'telegram')),
            'email' => env('TEGBOT_ALERT_EMAIL'),
            'slack_webhook' => env('TEGBOT_SLACK_WEBHOOK'),
            
            'thresholds' => [
                'error_rate' => env('TEGBOT_ALERT_ERROR_RATE', 5),
                'response_time' => env('TEGBOT_ALERT_RESPONSE_TIME', 2000),
                'memory_usage' => env('TEGBOT_ALERT_MEMORY', 80),
                'disk_usage' => env('TEGBOT_ALERT_DISK', 85),
            ],
            
            'cooldown_minutes' => env('TEGBOT_ALERT_COOLDOWN', 15),
        ],
        
        'metrics' => [
            'enabled' => env('TEGBOT_METRICS', true),
            'export_path' => env('TEGBOT_METRICS_PATH', '/metrics'),
            'prometheus_enabled' => env('TEGBOT_PROMETHEUS', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Интеграции
    |--------------------------------------------------------------------------
    */
    'integrations' => [
        'database' => [
            'enabled' => env('TEGBOT_DATABASE', true),
            'connection' => env('TEGBOT_DB_CONNECTION', 'mysql'),
            'user_tracking' => env('TEGBOT_USER_TRACKING', true),
            'message_storage' => env('TEGBOT_MESSAGE_STORAGE', false),
        ],
        
        'redis' => [
            'enabled' => env('TEGBOT_REDIS', true),
            'connection' => env('TEGBOT_REDIS_CONNECTION', 'default'),
        ],
        
        'elasticsearch' => [
            'enabled' => env('TEGBOT_ELASTICSEARCH', false),
            'host' => env('TEGBOT_ES_HOST', 'localhost:9200'),
            'index' => env('TEGBOT_ES_INDEX', 'tegbot-logs'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Локализация
    |--------------------------------------------------------------------------
    */
    'localization' => [
        'default_locale' => env('TEGBOT_LOCALE', 'ru'),
        'supported_locales' => explode(',', env('TEGBOT_LOCALES', 'ru,en')),
        'auto_detect' => env('TEGBOT_AUTO_LOCALE', true),
        'fallback' => env('TEGBOT_FALLBACK_LOCALE', 'en'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Экспериментальные функции
    |--------------------------------------------------------------------------
    */
    'experimental' => [
        'ai_responses' => env('TEGBOT_AI_RESPONSES', false),
        'auto_translation' => env('TEGBOT_AUTO_TRANSLATE', false),
        'voice_recognition' => env('TEGBOT_VOICE_RECOGNITION', false),
        'image_analysis' => env('TEGBOT_IMAGE_ANALYSIS', false),
    ],
];
```

## Переменные окружения

### Основной .env файл

```env
# Основные настройки TegBot
TEGBOT_TOKEN=your_bot_token_here
TEGBOT_WEBHOOK_SECRET=your_random_secret_32_chars
TEGBOT_DEBUG=false

# Администраторы
TEGBOT_ADMIN_IDS=123456789,987654321

# API настройки
TEGBOT_API_TIMEOUT=30
TEGBOT_API_RETRIES=3

# Безопасность
TEGBOT_SPAM_PROTECTION=true
TEGBOT_SPAM_LIMIT=20
TEGBOT_RATE_LIMIT_USER=20

# Файлы
TEGBOT_MAX_FILE_SIZE=20971520
TEGBOT_DOWNLOAD_PATH=/storage/app/tegbot/downloads

# Логирование
TEGBOT_LOGGING=true
TEGBOT_LOG_LEVEL=info
TEGBOT_LOG_RETENTION=30

# Кэширование
TEGBOT_CACHE=true
TEGBOT_CACHE_DRIVER=redis
TEGBOT_CACHE_TTL=3600

# Мониторинг
TEGBOT_HEALTH_CHECKS=true
TEGBOT_ALERTS=true
TEGBOT_ALERT_EMAIL=admin@example.com

# Интеграции
TEGBOT_DATABASE=true
TEGBOT_REDIS=true
```

### Переменные для разработки

```env
# Development настройки
TEGBOT_DEBUG=true
TEGBOT_LOG_LEVEL=debug
TEGBOT_CACHE=false
TEGBOT_QUEUE=false
TEGBOT_ALERTS=false

# Отладка
TEGBOT_VERBOSE_LOGGING=true
TEGBOT_DEBUG_TO_ADMINS=true
```

### Переменные для продакшена

```env
# Production настройки
TEGBOT_DEBUG=false
TEGBOT_LOG_LEVEL=warning
TEGBOT_CACHE=true
TEGBOT_QUEUE=true
TEGBOT_ALERTS=true

# Безопасность
TEGBOT_SPAM_PROTECTION=true
TEGBOT_FILTER_SQL=true
TEGBOT_BLOCK_HTML=true

# Производительность
TEGBOT_CACHE_DRIVER=redis
TEGBOT_QUEUE_CONNECTION=redis
```

## Настройки по окружениям

### config/tegbot/development.php

```php
<?php

return [
    'debug' => true,
    
    'api' => [
        'timeout' => 10,
        'retries' => 1,
    ],
    
    'security' => [
        'spam_protection' => [
            'enabled' => false,
        ],
        'rate_limits' => [
            'global' => 1000,
            'per_user' => 100,
        ],
    ],
    
    'logging' => [
        'level' => 'debug',
        'retention_days' => 7,
    ],
    
    'cache' => [
        'enabled' => false,
    ],
    
    'monitoring' => [
        'alerts' => [
            'enabled' => false,
        ],
    ],
];
```

### config/tegbot/production.php

```php
<?php

return [
    'debug' => false,
    
    'api' => [
        'timeout' => 30,
        'retries' => 3,
    ],
    
    'security' => [
        'spam_protection' => [
            'enabled' => true,
            'max_messages_per_minute' => 10,
        ],
        'validation' => [
            'max_message_length' => 2000,
            'block_html' => true,
            'filter_sql_injection' => true,
        ],
    ],
    
    'logging' => [
        'level' => 'warning',
        'retention_days' => 90,
    ],
    
    'cache' => [
        'enabled' => true,
        'driver' => 'redis',
        'ttl' => 7200,
    ],
    
    'monitoring' => [
        'alerts' => [
            'enabled' => true,
            'thresholds' => [
                'error_rate' => 3,
                'response_time' => 1500,
            ],
        ],
    ],
];
```

## Динамическая конфигурация

### Конфигурация из базы данных

```php
// app/Providers/TegBotConfigProvider.php
class TegBotConfigProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadDatabaseConfig();
    }
    
    private function loadDatabaseConfig()
    {
        try {
            $settings = DB::table('tegbot_settings')->pluck('value', 'key');
            
            foreach ($settings as $key => $value) {
                config(["tegbot.{$key}" => $this->parseValue($value)]);
            }
        } catch (Exception $e) {
            // База данных недоступна, используем значения по умолчанию
            Log::warning('TegBot: Could not load database config', ['error' => $e->getMessage()]);
        }
    }
    
    private function parseValue($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        
        if (in_array(strtolower($value), ['true', 'false'])) {
            return strtolower($value) === 'true';
        }
        
        if (str_contains($value, ',')) {
            return explode(',', $value);
        }
        
        return $value;
    }
}
```

### Управление конфигурацией через команды

```php
// Просмотр текущей конфигурации
php artisan teg:config

// Установка значения
php artisan teg:config:set spam_protection.max_messages_per_minute 15

// Получение значения
php artisan teg:config:get api.timeout

// Сброс к значениям по умолчанию
php artisan teg:config:reset

// Экспорт конфигурации
php artisan teg:config:export --format=json
```

## Валидация конфигурации

### Проверка настроек

```php
class ConfigValidator
{
    public function validate(): array
    {
        $errors = [];
        
        // Проверка обязательных параметров
        if (!config('tegbot.token')) {
            $errors[] = 'TEGBOT_TOKEN не установлен';
        }
        
        // Проверка формата токена
        if (!$this->isValidToken(config('tegbot.token'))) {
            $errors[] = 'Неверный формат токена бота';
        }
        
        // Проверка webhook secret
        if (!config('tegbot.security.webhook_secret')) {
            $errors[] = 'TEGBOT_WEBHOOK_SECRET не установлен (критично для безопасности)';
        }
        
        // Проверка админских ID
        $adminIds = config('tegbot.security.admin_ids');
        if (empty($adminIds)) {
            $errors[] = 'TEGBOT_ADMIN_IDS не указаны';
        } else {
            foreach ($adminIds as $id) {
                if (!is_numeric($id)) {
                    $errors[] = "Неверный формат admin ID: {$id}";
                }
            }
        }
        
        // Проверка путей
        $downloadPath = config('tegbot.files.download_path');
        if (!is_dir($downloadPath)) {
            $errors[] = "Путь для загрузок не существует: {$downloadPath}";
        } elseif (!is_writable($downloadPath)) {
            $errors[] = "Путь для загрузок недоступен для записи: {$downloadPath}";
        }
        
        // Проверка ограничений
        $maxFileSize = config('tegbot.files.max_file_size');
        if ($maxFileSize > 50 * 1024 * 1024) {
            $errors[] = "Слишком большой лимит размера файла: " . $this->formatBytes($maxFileSize);
        }
        
        return $errors;
    }
    
    private function isValidToken(string $token): bool
    {
        return preg_match('/^\d+:[A-Za-z0-9_-]{35}$/', $token);
    }
}

// Команда для валидации
php artisan teg:config:validate
```

## Оптимизация конфигурации

### Кэширование конфигурации

```php
// Команды для работы с кэшем конфигурации
php artisan config:cache    # Кэширование всех конфигов
php artisan teg:cache:config # Кэширование только TegBot конфигов
php artisan config:clear    # Очистка кэша конфигов
```

### Конфигурация для высокой нагрузки

```php
// config/tegbot/high-load.php
return [
    'api' => [
        'timeout' => 15,
        'retries' => 5,
        'retry_delay' => 2,
    ],
    
    'cache' => [
        'enabled' => true,
        'driver' => 'redis',
        'ttl' => 1800,
        'user_data_ttl' => 900,
    ],
    
    'queue' => [
        'enabled' => true,
        'connection' => 'redis',
        'timeout' => 30,
    ],
    
    'security' => [
        'rate_limits' => [
            'global' => 50,
            'per_user' => 10,
            'per_chat' => 25,
        ],
    ],
    
    'logging' => [
        'level' => 'error',
        'max_entries' => 5000,
        'retention_days' => 14,
    ],
];
```

## Примеры конфигураций

### E-commerce бот

```php
return [
    'security' => [
        'spam_protection' => [
            'max_messages_per_minute' => 30, // Больше для покупателей
        ],
        'rate_limits' => [
            'per_user' => 50,
            'commands' => 20,
        ],
    ],
    
    'files' => [
        'max_file_size' => 50 * 1024 * 1024, // 50MB для каталогов
        'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf'],
        'thumbnails' => [
            'enabled' => true,
            'quality' => 90,
        ],
    ],
    
    'integrations' => [
        'database' => [
            'user_tracking' => true,
            'message_storage' => true, // Для истории заказов
        ],
    ],
];
```

### Служебный бот

```php
return [
    'security' => [
        'admin_ids' => [123456789], // Только один админ
        'spam_protection' => [
            'enabled' => false, // Отключено для служебных задач
        ],
        'allowed_ips' => ['192.168.1.100'], // Только с определенного IP
    ],
    
    'api' => [
        'timeout' => 60, // Больше времени для служебных операций
        'retries' => 1,
    ],
    
    'logging' => [
        'level' => 'debug',
        'retention_days' => 180, // Длительное хранение логов
    ],
];
```

### Развлекательный бот

```php
return [
    'security' => [
        'spam_protection' => [
            'max_messages_per_minute' => 10, // Строже лимиты
        ],
        'rate_limits' => [
            'per_user' => 15,
            'commands' => 5,
        ],
    ],
    
    'files' => [
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'mp4'],
        'max_file_size' => 25 * 1024 * 1024,
        'auto_cleanup' => true,
        'cleanup_hours' => 6, // Быстрая очистка
    ],
    
    'experimental' => [
        'ai_responses' => true,
        'image_analysis' => true,
    ],
];
```

## Миграция конфигурации

### Обновление с версии 1.x

```php
// app/Console/Commands/MigrateConfig.php
class MigrateConfig extends Command
{
    protected $signature = 'teg:config:migrate {--from=1.0}';
    
    public function handle()
    {
        $fromVersion = $this->option('from');
        
        $this->info("Миграция конфигурации с версии {$fromVersion}");
        
        switch ($fromVersion) {
            case '1.0':
                $this->migrateFrom1x();
                break;
            default:
                $this->error("Неподдерживаемая версия: {$fromVersion}");
        }
    }
    
    private function migrateFrom1x()
    {
        $oldConfig = config('telegram');
        
        if ($oldConfig) {
            $newConfig = [
                'TEGBOT_TOKEN' => $oldConfig['token'] ?? '',
                'TEGBOT_ADMIN_IDS' => implode(',', $oldConfig['admins'] ?? []),
                'TEGBOT_WEBHOOK_SECRET' => Str::random(32),
            ];
            
            $this->writeEnvFile($newConfig);
            $this->info('Конфигурация успешно мигрирована');
        }
    }
}
```

---

⚙️ **Конфигурация TegBot** - гибкость для любых сценариев использования! 