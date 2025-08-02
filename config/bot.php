<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bot Configuration
    |--------------------------------------------------------------------------
    |
    | Конфигурация для пакета Bot - фреймворка для создания Telegram ботов
    | с поддержкой мультибота
    |
    */

    /**
     * Режим отладки
     */
    'debug' => env('BOT_DEBUG', false),

    /**
     * Часовой пояс для бота
     */
    'timezone' => env('BOT_TIMEZONE', config('app.timezone', 'UTC')),

    /*
    |--------------------------------------------------------------------------
    | Multi-Bot Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки для мультиботной архитектуры. Боты хранятся в базе данных
    | и управляются через команды php artisan bot:set и php artisan bot:bot
    |
    */
    'multibot' => [
        /**
         * Включить мультиботную систему
         */
        'enabled' => env('BOT_MULTIBOT_ENABLED', true),

        /**
         * Автоматически создавать классы ботов
         */
        'auto_create_classes' => env('BOT_AUTO_CREATE_CLASSES', true),

        /**
         * Путь для классов ботов
         */
        'bots_path' => env('BOT_BOTS_PATH', 'App\\Bots'),

        /**
         * Namespace для классов ботов
         */
        'bots_namespace' => env('BOT_BOTS_NAMESPACE', 'App\\Bots'),

        /**
         * Максимальное количество ботов
         */
        'max_bots' => env('BOT_MAX_BOTS', 100),

        /**
         * Автоматически активировать новых ботов
         */
        'auto_enable' => env('BOT_AUTO_ENABLE_BOTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    */
    'api' => [
        /**
         * Базовый URL Telegram Bot API
         */
        'base_url' => env('BOT_API_URL', 'https://api.telegram.org'),

        /**
         * Таймаут для API запросов (секунды)
         */
        'timeout' => env('BOT_API_TIMEOUT', 30),

        /**
         * Количество повторных попыток при ошибках
         */
        'retries' => env('BOT_API_RETRIES', 3),

        /**
         * Задержка между повторными попытками (секунды)
         */
        'retry_delay' => env('BOT_API_RETRY_DELAY', 1),

        /**
         * User-Agent для запросов
         */
        'user_agent' => env('BOT_USER_AGENT', 'Bot/2.0 Laravel Bot Framework'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        /**
         * Webhook secret token для верификации запросов (глобальный)
         */
        'webhook_secret' => env('BOT_WEBHOOK_SECRET'),

        /**
         * ID администраторов бота (разделённые запятыми) - глобальные
         */
        'admin_ids' => array_filter(explode(',', env('BOT_ADMIN_IDS', ''))),

        /**
         * Разрешённые IP адреса для webhook (пусто = все)
         */
        'allowed_ips' => array_filter(explode(',', env('BOT_ALLOWED_IPS', ''))),

        /**
         * Защита от спама
         */
        'spam_protection' => [
            'enabled' => env('BOT_SPAM_PROTECTION', true),
            'max_messages_per_minute' => env('BOT_SPAM_MAX_MESSAGES', 20),
        ],

        /**
         * Лимиты скорости
         */
        'rate_limits' => [
            'global' => env('BOT_RATE_LIMIT_GLOBAL', 30), // запросов в секунду
            'per_user' => env('BOT_RATE_LIMIT_PER_USER', 1), // запросов в секунду на пользователя
            'per_chat' => env('BOT_RATE_LIMIT_PER_CHAT', 5), // запросов в секунду на чат
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Handling
    |--------------------------------------------------------------------------
    */
    'files' => [
        /**
         * Путь для загрузки файлов
         */
        'download_path' => env('BOT_DOWNLOAD_PATH', storage_path('app/bot/downloads')),

        /**
         * Путь для временных файлов
         */
        'temp_path' => env('BOT_TEMP_PATH', storage_path('app/bot/temp')),

        /**
         * Максимальный размер файла для загрузки (байты)
         */
        'max_file_size' => env('BOT_MAX_FILE_SIZE', 50 * 1024 * 1024), // 50MB

        /**
         * Разрешённые типы файлов
         */
        'allowed_types' => array_filter(explode(',', env('BOT_ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx,mp4,mp3'))),

        /**
         * Автоматически удалять файлы старше N дней
         */
        'auto_cleanup_days' => env('BOT_FILE_CLEANUP_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    */
    'logging' => [
        /**
         * Включить детальное логирование
         */
        'enabled' => env('BOT_LOGGING', true),

        /**
         * Уровень логирования
         */
        'level' => env('BOT_LOG_LEVEL', 'info'),

        /**
         * Хранить логи N дней
         */
        'retention_days' => env('BOT_LOG_RETENTION', 30),

        /**
         * Логировать входящие сообщения
         */
        'log_incoming' => env('BOT_LOG_INCOMING', true),

        /**
         * Логировать исходящие сообщения
         */
        'log_outgoing' => env('BOT_LOG_OUTGOING', false),

        /**
         * Логировать ошибки API
         */
        'log_api_errors' => env('BOT_LOG_API_ERRORS', true),

        /**
         * Логировать действия мультибота
         */
        'log_multibot' => env('BOT_LOG_MULTIBOT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        /**
         * Включить кэширование
         */
        'enabled' => env('BOT_CACHE_ENABLED', true),

        /**
         * Драйвер кэша
         */
        'driver' => env('BOT_CACHE_DRIVER', 'file'),

        /**
         * Время жизни кэша по умолчанию (секунды)
         */
        'ttl' => env('BOT_CACHE_TTL', 3600),

        /**
         * Префикс для ключей кэша
         */
        'prefix' => env('BOT_CACHE_PREFIX', 'bot'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    */
    'database' => [
        /**
         * Использовать базу данных для хранения состояний
         */
        'enabled' => env('BOT_DATABASE_ENABLED', true),

        /**
         * Соединение с БД
         */
        'connection' => env('BOT_DB_CONNECTION', 'default'),

        /**
         * Префикс таблиц
         */
        'table_prefix' => env('BOT_TABLE_PREFIX', 'bot_'),

        /**
         * Автоматическая очистка старых данных
         */
        'auto_cleanup' => env('BOT_DB_AUTO_CLEANUP', true),

        /**
         * Хранить историю команд ботов
         */
        'store_commands_history' => env('BOT_STORE_COMMANDS_HISTORY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    */
    'webhook' => [
        /**
         * URL webhook (устанавливается автоматически)
         */
        'url' => env('BOT_WEBHOOK_URL'),

        /**
         * Максимальное количество соединений
         */
        'max_connections' => env('BOT_WEBHOOK_MAX_CONNECTIONS', 40),

        /**
         * Типы обновлений для получения
         */
        'allowed_updates' => array_filter(explode(',', env('BOT_WEBHOOK_UPDATES', 'message,callback_query,inline_query'))),

        /**
         * Автоматически генерировать webhook secret для новых ботов
         */
        'auto_generate_secret' => env('BOT_AUTO_GENERATE_WEBHOOK_SECRET', true),

        /**
         * Базовый URL для webhook'ов (если отличается от APP_URL)
         */
        'base_url' => env('BOT_WEBHOOK_BASE_URL', env('APP_URL')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    */
    'performance' => [
        /**
         * Лимит памяти для обработки файлов
         */
        'memory_limit' => env('BOT_MEMORY_LIMIT', '256M'),

        /**
         * Максимальное время выполнения скрипта
         */
        'max_execution_time' => env('BOT_MAX_EXECUTION_TIME', 30),

        /**
         * Пакетная обработка сообщений
         */
        'batch_processing' => env('BOT_BATCH_PROCESSING', false),

        /**
         * Размер пакета
         */
        'batch_size' => env('BOT_BATCH_SIZE', 10),

        /**
         * Кэширование информации о ботах
         */
        'cache_bot_info' => env('BOT_CACHE_BOT_INFO', true),

        /**
         * Время кэширования информации о ботах (секунды)
         */
        'cache_bot_info_ttl' => env('BOT_CACHE_BOT_INFO_TTL', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring & Health Checks
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        /**
         * Включить проверки здоровья
         */
        'health_checks' => [
            'enabled' => env('BOT_HEALTH_CHECKS', true),
            'interval' => env('BOT_HEALTH_CHECK_INTERVAL', 300), // 5 минут
            'check_all_bots' => env('BOT_HEALTH_CHECK_ALL_BOTS', true),
        ],

        /**
         * Алерты и уведомления
         */
        'alerts' => [
            'enabled' => env('BOT_ALERTS_ENABLED', false),
            'email' => env('BOT_ALERTS_EMAIL'),
            'telegram_chat_id' => env('BOT_ALERTS_CHAT_ID'),
        ],

        /**
         * Метрики
         */
        'metrics' => [
            'enabled' => env('BOT_METRICS_ENABLED', true),
            'retention_days' => env('BOT_METRICS_RETENTION', 30),
            'track_per_bot' => env('BOT_METRICS_PER_BOT', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Experimental Features
    |--------------------------------------------------------------------------
    */
    'experimental' => [
        /**
         * Включить экспериментальные возможности
         */
        'enabled' => env('BOT_EXPERIMENTAL', false),

        /**
         * Асинхронная обработка
         */
        'async_processing' => env('BOT_ASYNC_PROCESSING', false),

        /**
         * AI интеграция
         */
        'ai_integration' => env('BOT_AI_INTEGRATION', false),

        /**
         * Автоматическое обновление webhook'ов при изменении конфигурации
         */
        'auto_update_webhooks' => env('BOT_AUTO_UPDATE_WEBHOOKS', false),
    ],
]; 