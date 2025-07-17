# ⚙️ Конфигурация TegBot v2.0

## Обзор

TegBot v2.0 использует новую мультиботную архитектуру с гибкой системой конфигурации:

- 🤖 **Мультибот управление**: Конфигурация отдельных ботов через базу данных
- ⚙️ **Глобальные настройки**: Общие параметры системы в файлах конфигурации
- 🛡️ **Безопасность**: Многоуровневая защита и ограничения
- 📊 **Мониторинг**: Диагностика и метрики для каждого бота
- 🚀 **Производительность**: Кэширование, очереди, оптимизация
- 🔧 **Управление**: Команды artisan для всех настроек

## Новая архитектура конфигурации

### ⚠️ Важные изменения в v2.0:

1. **Токены ботов** - хранятся в базе данных, НЕ в .env
2. **Настройки ботов** - индивидуальные для каждого бота
3. **Глобальные настройки** - общие для всей системы
4. **Команды управления** - все через artisan команды

## Основной файл конфигурации

### config/tegbot.php

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TegBot v2.0 - Мультиботная система
    |--------------------------------------------------------------------------
    */
    
    /**
     * Мультибот настройки
     */
    'multibot' => [
        'enabled' => env('TEGBOT_MULTIBOT_ENABLED', true),
        'auto_create_classes' => env('TEGBOT_AUTO_CREATE_CLASSES', true),
        'bots_path' => env('TEGBOT_BOTS_PATH', 'App\\Bots'),
        'max_bots' => env('TEGBOT_MAX_BOTS', 100),
        'auto_enable' => env('TEGBOT_AUTO_ENABLE_BOTS', true),
    ],

    /**
     * Глобальные настройки системы
     */
    'debug' => env('TEGBOT_DEBUG', false),
    'timezone' => env('TEGBOT_TIMEZONE', config('app.timezone', 'UTC')),

    /*
    |--------------------------------------------------------------------------
    | API настройки (для всех ботов)
    |--------------------------------------------------------------------------
    */
    'api' => [
        'base_url' => env('TEGBOT_API_URL', 'https://api.telegram.org'),
        'timeout' => env('TEGBOT_API_TIMEOUT', 30),
        'retries' => env('TEGBOT_API_RETRIES', 3),
        'retry_delay' => env('TEGBOT_API_RETRY_DELAY', 1),
        'rate_limit_delay' => env('TEGBOT_API_RATE_LIMIT_DELAY', 5),
        'user_agent' => env('TEGBOT_USER_AGENT', 'TegBot/2.0 Laravel Bot Framework'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Безопасность (глобальная)
    |--------------------------------------------------------------------------
    */
    'security' => [
        'webhook_secret' => env('TEGBOT_WEBHOOK_SECRET'),
        'auto_generate_webhook_secret' => env('TEGBOT_AUTO_GENERATE_WEBHOOK_SECRET', true),
        'allowed_ips' => array_filter(explode(',', env('TEGBOT_ALLOWED_IPS', ''))),
        
        'spam_protection' => [
            'enabled' => env('TEGBOT_SPAM_PROTECTION', true),
            'max_messages_per_minute' => env('TEGBOT_SPAM_LIMIT', 20),
            'ban_duration_minutes' => env('TEGBOT_SPAM_BAN_DURATION', 60),
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
    | Webhook настройки
    |--------------------------------------------------------------------------
    */
    'webhook' => [
        'base_url' => env('TEGBOT_WEBHOOK_BASE_URL', config('app.url')),
        'path_prefix' => env('TEGBOT_WEBHOOK_PATH', '/webhook'),
        'auto_setup' => env('TEGBOT_AUTO_SETUP_WEBHOOKS', false),
        'ssl_verify' => env('TEGBOT_WEBHOOK_SSL_VERIFY', true),
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
        'multibot_logs' => env('TEGBOT_LOG_MULTIBOT', true),
        'store_commands_history' => env('TEGBOT_STORE_COMMANDS_HISTORY', true),
        
        'channels' => [
            'default' => env('TEGBOT_LOG_CHANNEL', 'stack'),
            'errors' => env('TEGBOT_ERROR_CHANNEL', 'daily'),
            'activity' => env('TEGBOT_ACTIVITY_CHANNEL', 'tegbot_activity'),
            'security' => env('TEGBOT_SECURITY_CHANNEL', 'tegbot_security'),
        ],
        
        'retention_days' => env('TEGBOT_LOG_RETENTION', 30),
        'max_entries' => env('TEGBOT_LOG_MAX_ENTRIES', 10000),
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
        
        // Кэширование информации о ботах
        'bot_info' => [
            'enabled' => env('TEGBOT_CACHE_BOT_INFO', true),
            'ttl' => env('TEGBOT_CACHE_BOT_INFO_TTL', 3600),
        ],
        
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
    | Мониторинг (для всех ботов)
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'health_checks' => [
            'enabled' => env('TEGBOT_HEALTH_CHECKS', true),
            'interval_minutes' => env('TEGBOT_HEALTH_INTERVAL', 5),
            'timeout' => env('TEGBOT_HEALTH_TIMEOUT', 10),
            'per_bot_checks' => env('TEGBOT_PER_BOT_HEALTH', true),
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
            'per_bot_metrics' => env('TEGBOT_METRICS_PER_BOT', true),
            'export_path' => env('TEGBOT_METRICS_PATH', '/metrics'),
            'prometheus_enabled' => env('TEGBOT_PROMETHEUS', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | База данных (мультибот)
    |--------------------------------------------------------------------------
    */
    'database' => [
        'connection' => env('TEGBOT_DB_CONNECTION', config('database.default')),
        'bots_table' => env('TEGBOT_BOTS_TABLE', 'tegbot_bots'),
        'messages_table' => env('TEGBOT_MESSAGES_TABLE', 'messages'),
        'users_table' => env('TEGBOT_USERS_TABLE', 'users'),
        
        'user_tracking' => env('TEGBOT_USER_TRACKING', true),
        'message_storage' => env('TEGBOT_MESSAGE_STORAGE', false),
        'command_history' => env('TEGBOT_COMMAND_HISTORY', true),
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

### Основной .env файл (без токенов!)

```env
# ==========================================
# TegBot v2.0 - Мультиботная конфигурация
# ==========================================

# Мультибот настройки
TEGBOT_MULTIBOT_ENABLED=true
TEGBOT_AUTO_CREATE_CLASSES=true
TEGBOT_MAX_BOTS=100
TEGBOT_AUTO_ENABLE_BOTS=true

# Глобальные настройки
TEGBOT_DEBUG=false
TEGBOT_WEBHOOK_SECRET=your_random_secret_32_chars

# Webhook настройки
TEGBOT_WEBHOOK_BASE_URL=https://yourdomain.com
TEGBOT_AUTO_GENERATE_WEBHOOK_SECRET=true
TEGBOT_AUTO_SETUP_WEBHOOKS=false

# API настройки (для всех ботов)
TEGBOT_API_TIMEOUT=30
TEGBOT_API_RETRIES=3
TEGBOT_API_RETRY_DELAY=1

# Безопасность (глобальная)
TEGBOT_SPAM_PROTECTION=true
TEGBOT_SPAM_LIMIT=20
TEGBOT_RATE_LIMIT_USER=20
TEGBOT_RATE_LIMIT_GLOBAL=100

# Файлы и медиа
TEGBOT_MAX_FILE_SIZE=20971520
TEGBOT_DOWNLOAD_PATH=/storage/app/tegbot/downloads
TEGBOT_AUTO_CLEANUP=true
TEGBOT_CLEANUP_HOURS=24

# Логирование
TEGBOT_LOGGING=true
TEGBOT_LOG_LEVEL=info
TEGBOT_LOG_MULTIBOT=true
TEGBOT_STORE_COMMANDS_HISTORY=true
TEGBOT_LOG_RETENTION=30

# Кэширование
TEGBOT_CACHE=true
TEGBOT_CACHE_DRIVER=redis
TEGBOT_CACHE_TTL=3600
TEGBOT_CACHE_BOT_INFO=true
TEGBOT_CACHE_BOT_INFO_TTL=3600

# Мониторинг
TEGBOT_HEALTH_CHECKS=true
TEGBOT_PER_BOT_HEALTH=true
TEGBOT_ALERTS=true
TEGBOT_METRICS_PER_BOT=true

# База данных
TEGBOT_USER_TRACKING=true
TEGBOT_MESSAGE_STORAGE=false
TEGBOT_COMMAND_HISTORY=true
```

⚠️ **ВАЖНО:** Токены ботов больше НЕ хранятся в .env! Они управляются через команды artisan.

### Переменные для разработки

```env
# Development настройки
TEGBOT_DEBUG=true
TEGBOT_LOG_LEVEL=debug
TEGBOT_LOG_MULTIBOT=true
TEGBOT_CACHE=false
TEGBOT_QUEUE=false
TEGBOT_ALERTS=false

# Отладка
TEGBOT_VERBOSE_LOGGING=true
TEGBOT_SPAM_PROTECTION=false
TEGBOT_AUTO_SETUP_WEBHOOKS=true
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
TEGBOT_AUTO_SETUP_WEBHOOKS=false

# Производительность
TEGBOT_CACHE_DRIVER=redis
TEGBOT_QUEUE_CONNECTION=redis
TEGBOT_CACHE_BOT_INFO=true
```

## Управление конфигурацией через команды

### Команды для работы с конфигурацией

```bash
# Просмотр всей конфигурации
php artisan teg:config show

# Просмотр конкретного параметра
php artisan teg:config get multibot.enabled

# Установка параметра
php artisan teg:config set multibot.max_bots 200

# Сброс к значениям по умолчанию
php artisan teg:config reset

# Валидация конфигурации
php artisan teg:config validate

# Экспорт конфигурации
php artisan teg:config export --format=json
```

### Конфигурация отдельных ботов

Каждый бот имеет свои настройки в базе данных:

```bash
# Просмотр настроек бота
php artisan teg:bot show myshop

# Изменение настроек бота
php artisan teg:bot config myshop --setting=rate_limit --value=30
php artisan teg:bot config myshop --setting=language --value=en

# Настройка администраторов бота
php artisan teg:bot admin myshop --add=123456789
php artisan teg:bot admin myshop --remove=987654321
```

## База данных ботов

### Структура таблицы tegbot_bots

```sql
CREATE TABLE tegbot_bots (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) UNIQUE,           -- Имя бота (shop, news, support)
    token VARCHAR(255) UNIQUE,          -- Токен от BotFather  
    username VARCHAR(255),              -- Username бота (@shopbot)
    first_name VARCHAR(255),            -- Имя бота
    description TEXT,                   -- Описание бота
    bot_id BIGINT UNIQUE,              -- ID бота в Telegram
    enabled BOOLEAN DEFAULT TRUE,       -- Активен ли бот
    webhook_url VARCHAR(255),           -- URL webhook
    webhook_secret VARCHAR(255),        -- Секрет webhook (индивидуальный)
    settings JSON,                      -- Дополнительные настройки бота
    admin_ids JSON,                     -- ID администраторов бота
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Пример настроек бота в JSON

```json
{
    "language": "ru",
    "timezone": "Europe/Moscow",
    "features": ["payments", "inline_queries"],
    "rate_limit": 30,
    "spam_protection": {
        "enabled": true,
        "max_messages": 15
    },
    "auto_responses": true,
    "debug_mode": false,
    "custom_commands": {
        "welcome_message": "Добро пожаловать в наш магазин!",
        "help_text": "Доступные команды: /start, /catalog, /help"
    }
}
```

## Конфигурация по типам ботов

### E-commerce бот

```bash
# Создание бота с расширенными настройками
php artisan teg:set

# После создания настройка специфичных параметров
php artisan teg:bot config shop --setting=rate_limit --value=50
php artisan teg:bot config shop --setting=features --value='["payments","inline_queries","webhooks"]'
php artisan teg:bot config shop --setting=language --value=ru
php artisan teg:bot config shop --setting=timezone --value="Europe/Moscow"
```

```json
// Настройки в базе данных для e-commerce бота
{
    "language": "ru",
    "timezone": "Europe/Moscow", 
    "features": ["payments", "inline_queries", "webhooks"],
    "rate_limit": 50,
    "max_file_size": 52428800,
    "allowed_file_types": ["jpg", "jpeg", "png", "pdf"],
    "payment_provider": "sberbank",
    "currency": "RUB",
    "auto_responses": true,
    "catalog_mode": "inline",
    "order_notifications": true
}
```

### Новостной бот

```bash
# Настройка новостного бота
php artisan teg:bot config news --setting=broadcast_mode --value=true
php artisan teg:bot config news --setting=max_subscribers --value=10000
php artisan teg:bot config news --setting=post_interval --value=300
```

```json
{
    "language": "ru",
    "broadcast_mode": true,
    "max_subscribers": 10000,
    "post_interval": 300,
    "auto_post": true,
    "categories": ["tech", "business", "sport"],
    "moderation": true,
    "analytics": true
}
```

### Служба поддержки

```bash
# Настройка бота поддержки
php artisan teg:bot config support --setting=ticket_system --value=true
php artisan teg:bot config support --setting=auto_assignment --value=true
php artisan teg:bot config support --setting=working_hours --value='{"start":"09:00","end":"18:00"}'
```

```json
{
    "language": "ru",
    "ticket_system": true,
    "auto_assignment": true,
    "working_hours": {
        "start": "09:00",
        "end": "18:00",
        "timezone": "Europe/Moscow",
        "days": ["mon", "tue", "wed", "thu", "fri"]
    },
    "queue_system": true,
    "priority_levels": ["low", "normal", "high", "urgent"],
    "auto_responses": {
        "greeting": true,
        "working_hours": true,
        "queue_position": true
    }
}
```

## Валидация конфигурации

### Автоматическая проверка

```bash
# Полная проверка конфигурации
php artisan teg:config validate

# Проверка конкретного бота
php artisan teg:bot validate myshop

# Проверка всех ботов
php artisan teg:bot validate --all
```

### Пример вывода валидации

```
⚙️  Валидация конфигурации TegBot

✅ Глобальная конфигурация:
  ✅ Webhook secret установлен
  ✅ Пути для файлов существуют и доступны для записи
  ✅ Redis подключение работает
  ✅ База данных доступна

🤖 Проверка ботов:
  ✅ shop (@shopbot): Конфигурация корректна
  ✅ news (@newsbot): Конфигурация корректна  
  ❌ support (@supportbot): Ошибки в настройках:
    - Неверный формат working_hours
    - Администраторы не указаны

⚠️  Предупреждения:
  - TEGBOT_CACHE_DRIVER=file рекомендуется Redis для продакшена
  - TEGBOT_QUEUE=false рекомендуется включить для высокой нагрузки

Проверено: 3 бота, найдено: 2 ошибки, 2 предупреждения
```

## Продакшен настройки

### Оптимизация производительности

```env
# Высокая производительность
TEGBOT_CACHE=true
TEGBOT_CACHE_DRIVER=redis
TEGBOT_CACHE_BOT_INFO=true
TEGBOT_CACHE_BOT_INFO_TTL=7200

# Очереди для масштабирования
TEGBOT_QUEUE=true
TEGBOT_QUEUE_CONNECTION=redis

# Оптимизированное логирование
TEGBOT_LOG_LEVEL=warning
TEGBOT_STORE_COMMANDS_HISTORY=false
TEGBOT_LOG_RETENTION=14
```

### Безопасность продакшена

```env
# Строгая безопасность
TEGBOT_DEBUG=false
TEGBOT_SPAM_PROTECTION=true
TEGBOT_RATE_LIMIT_GLOBAL=50
TEGBOT_FILTER_SQL=true
TEGBOT_BLOCK_HTML=true

# Ограничение доступа
TEGBOT_ALLOWED_IPS=149.154.160.0/20,91.108.4.0/22
TEGBOT_AUTO_SETUP_WEBHOOKS=false
```

### Мониторинг продакшена

```env
# Полный мониторинг
TEGBOT_HEALTH_CHECKS=true
TEGBOT_PER_BOT_HEALTH=true
TEGBOT_ALERTS=true
TEGBOT_METRICS_PER_BOT=true

# Настройки алертов
TEGBOT_ALERT_EMAIL=admin@yourdomain.com
TEGBOT_ALERT_ERROR_RATE=3
TEGBOT_ALERT_RESPONSE_TIME=1500
```

## Команды для управления

### Основные команды

```bash
# Глобальная конфигурация
php artisan teg:config show                    # Показать всю конфигурацию
php artisan teg:config get security.spam_protection   # Получить значение
php artisan teg:config set cache.ttl 7200     # Установить значение

# Конфигурация ботов
php artisan teg:bot list                       # Список всех ботов
php artisan teg:bot show myshop               # Настройки конкретного бота
php artisan teg:bot config myshop --setting=language --value=en

# Валидация
php artisan teg:config validate              # Проверить глобальную конфигурацию
php artisan teg:bot validate myshop          # Проверить конкретного бота
php artisan teg:bot validate --all           # Проверить всех ботов

# Импорт/экспорт
php artisan teg:config export --format=json  # Экспорт в JSON
php artisan teg:migrate export               # Резервная копия ботов
php artisan teg:migrate import backup.json   # Восстановление ботов
```

### Автоматизация

```bash
# Cron для автоматической проверки
# Каждые 15 минут проверяем конфигурацию
*/15 * * * * cd /path/to/project && php artisan teg:config validate --quiet

# Ежедневная проверка всех ботов
0 2 * * * cd /path/to/project && php artisan teg:bot validate --all --fix

# Еженедельная очистка логов
0 3 * * 0 cd /path/to/project && php artisan teg:logs clean
```

## Миграция с TegBot v1.x

⚠️ **ВНИМАНИЕ: Полная несовместимость с v1.x!**

### Процесс миграции конфигурации

```bash
# 1. Сохраните старую конфигурацию
cp config/telegram.php config/telegram.php.backup

# 2. Обновите пакет
composer update tegbot/tegbot

# 3. Публикация новой конфигурации
php artisan vendor:publish --provider="Teg\Providers\TegbotServiceProvider" --force

# 4. Запуск миграций базы данных
php artisan migrate

# 5. Перенос ботов в новую систему
php artisan teg:migrate legacy --from=config/telegram.php.backup

# 6. Проверка результата
php artisan teg:config validate
php artisan teg:bot list
```

### Что изменилось

| v1.x | v2.0 |
|------|------|
| `TELEGRAM_TOKEN` | Токены в базе данных |
| `config/telegram.php` | `config/tegbot.php` + БД |
| Один бот | Множественные боты |
| Статическая конфигурация | Динамическая через команды |
| `/bot/{token}` | `/webhook/{botName}` |

---

⚙️ **Конфигурация TegBot v2.0** - полная гибкость для мультиботной системы! 