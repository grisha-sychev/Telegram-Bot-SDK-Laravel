<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TegBot Configuration
    |--------------------------------------------------------------------------
    |
    | Конфигурация для пакета TegBot - фреймворка для создания Telegram ботов
    | с поддержкой мультибота
    |
    */

    /**
     * Токен бота Telegram (для обратной совместимости)
     * Получите в @BotFather
     */
    'token' => env('TEGBOT_TOKEN'),

    /**
     * Режим отладки
     */
    'debug' => env('TEGBOT_DEBUG', false),

    /**
     * Часовой пояс для бота
     */
    'timezone' => env('TEGBOT_TIMEZONE', config('app.timezone', 'UTC')),

    /*
    |--------------------------------------------------------------------------
    | Multi-Bot Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки для мультиботной архитектуры. Боты хранятся в базе данных
    | и управляются через команды php artisan teg:set и php artisan teg:bot
    |
    */
    'multibot' => [
        /**
         * Включить мультиботную систему
         */
        'enabled' => env('TEGBOT_MULTIBOT_ENABLED', true),

        /**
         * Автоматически создавать классы ботов
         */
        'auto_create_classes' => env('TEGBOT_AUTO_CREATE_CLASSES', true),

        /**
         * Путь для классов ботов
         */
        'bots_path' => env('TEGBOT_BOTS_PATH', 'App\\Bots'),

        /**
         * Namespace для классов ботов
         */
        'bots_namespace' => env('TEGBOT_BOTS_NAMESPACE', 'App\\Bots'),

        /**
         * Максимальное количество ботов
         */
        'max_bots' => env('TEGBOT_MAX_BOTS', 100),

        /**
         * Автоматически активировать новых ботов
         */
        'auto_enable' => env('TEGBOT_AUTO_ENABLE_BOTS', true),
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
        'base_url' => env('TEGBOT_API_URL', 'https://api.telegram.org'),

        /**
         * Таймаут для API запросов (секунды)
         */
        'timeout' => env('TEGBOT_API_TIMEOUT', 30),

        /**
         * Количество повторных попыток при ошибках
         */
        'retries' => env('TEGBOT_API_RETRIES', 3),

        /**
         * Задержка между повторными попытками (секунды)
         */
        'retry_delay' => env('TEGBOT_API_RETRY_DELAY', 1),

        /**
         * User-Agent для запросов
         */
        'user_agent' => env('TEGBOT_USER_AGENT', 'TegBot/2.0 Laravel Bot Framework'),
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
        'webhook_secret' => env('TEGBOT_WEBHOOK_SECRET'),

        /**
         * ID администраторов бота (разделённые запятыми) - глобальные
         */
        'admin_ids' => array_filter(explode(',', env('TEGBOT_ADMIN_IDS', ''))),

        /**
         * Разрешённые IP адреса для webhook (пусто = все)
         */
        'allowed_ips' => array_filter(explode(',', env('TEGBOT_ALLOWED_IPS', ''))),

        /**
         * Защита от спама
         */
        'spam_protection' => [
            'enabled' => env('TEGBOT_SPAM_PROTECTION', true),
            'max_messages_per_minute' => env('TEGBOT_SPAM_MAX_MESSAGES', 20),
        ],

        /**
         * Лимиты скорости
         */
        'rate_limits' => [
            'global' => env('TEGBOT_RATE_LIMIT_GLOBAL', 30), // запросов в секунду
            'per_user' => env('TEGBOT_RATE_LIMIT_PER_USER', 1), // запросов в секунду на пользователя
            'per_chat' => env('TEGBOT_RATE_LIMIT_PER_CHAT', 5), // запросов в секунду на чат
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
        'download_path' => env('TEGBOT_DOWNLOAD_PATH', storage_path('app/tegbot/downloads')),

        /**
         * Путь для временных файлов
         */
        'temp_path' => env('TEGBOT_TEMP_PATH', storage_path('app/tegbot/temp')),

        /**
         * Максимальный размер файла для загрузки (байты)
         */
        'max_file_size' => env('TEGBOT_MAX_FILE_SIZE', 50 * 1024 * 1024), // 50MB

        /**
         * Разрешённые типы файлов
         */
        'allowed_types' => array_filter(explode(',', env('TEGBOT_ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx,mp4,mp3'))),

        /**
         * Автоматически удалять файлы старше N дней
         */
        'auto_cleanup_days' => env('TEGBOT_FILE_CLEANUP_DAYS', 30),
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
        'enabled' => env('TEGBOT_LOGGING', true),

        /**
         * Уровень логирования
         */
        'level' => env('TEGBOT_LOG_LEVEL', 'info'),

        /**
         * Хранить логи N дней
         */
        'retention_days' => env('TEGBOT_LOG_RETENTION', 30),

        /**
         * Логировать входящие сообщения
         */
        'log_incoming' => env('TEGBOT_LOG_INCOMING', true),

        /**
         * Логировать исходящие сообщения
         */
        'log_outgoing' => env('TEGBOT_LOG_OUTGOING', false),

        /**
         * Логировать ошибки API
         */
        'log_api_errors' => env('TEGBOT_LOG_API_ERRORS', true),

        /**
         * Логировать действия мультибота
         */
        'log_multibot' => env('TEGBOT_LOG_MULTIBOT', true),
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
        'enabled' => env('TEGBOT_CACHE_ENABLED', true),

        /**
         * Драйвер кэша
         */
        'driver' => env('TEGBOT_CACHE_DRIVER', 'file'),

        /**
         * Время жизни кэша по умолчанию (секунды)
         */
        'ttl' => env('TEGBOT_CACHE_TTL', 3600),

        /**
         * Префикс для ключей кэша
         */
        'prefix' => env('TEGBOT_CACHE_PREFIX', 'tegbot'),
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
        'enabled' => env('TEGBOT_DATABASE_ENABLED', true),

        /**
         * Соединение с БД
         */
        'connection' => env('TEGBOT_DB_CONNECTION', 'default'),

        /**
         * Префикс таблиц
         */
        'table_prefix' => env('TEGBOT_TABLE_PREFIX', 'tegbot_'),

        /**
         * Автоматическая очистка старых данных
         */
        'auto_cleanup' => env('TEGBOT_DB_AUTO_CLEANUP', true),

        /**
         * Хранить историю команд ботов
         */
        'store_commands_history' => env('TEGBOT_STORE_COMMANDS_HISTORY', true),
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
        'url' => env('TEGBOT_WEBHOOK_URL'),

        /**
         * Максимальное количество соединений
         */
        'max_connections' => env('TEGBOT_WEBHOOK_MAX_CONNECTIONS', 40),

        /**
         * Типы обновлений для получения
         */
        'allowed_updates' => array_filter(explode(',', env('TEGBOT_WEBHOOK_UPDATES', 'message,callback_query,inline_query'))),

        /**
         * Автоматически генерировать webhook secret для новых ботов
         */
        'auto_generate_secret' => env('TEGBOT_AUTO_GENERATE_WEBHOOK_SECRET', true),

        /**
         * Базовый URL для webhook'ов (если отличается от APP_URL)
         */
        'base_url' => env('TEGBOT_WEBHOOK_BASE_URL', env('APP_URL')),
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
        'memory_limit' => env('TEGBOT_MEMORY_LIMIT', '256M'),

        /**
         * Максимальное время выполнения скрипта
         */
        'max_execution_time' => env('TEGBOT_MAX_EXECUTION_TIME', 30),

        /**
         * Пакетная обработка сообщений
         */
        'batch_processing' => env('TEGBOT_BATCH_PROCESSING', false),

        /**
         * Размер пакета
         */
        'batch_size' => env('TEGBOT_BATCH_SIZE', 10),

        /**
         * Кэширование информации о ботах
         */
        'cache_bot_info' => env('TEGBOT_CACHE_BOT_INFO', true),

        /**
         * Время кэширования информации о ботах (секунды)
         */
        'cache_bot_info_ttl' => env('TEGBOT_CACHE_BOT_INFO_TTL', 3600),
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
            'enabled' => env('TEGBOT_HEALTH_CHECKS', true),
            'interval' => env('TEGBOT_HEALTH_CHECK_INTERVAL', 300), // 5 минут
            'check_all_bots' => env('TEGBOT_HEALTH_CHECK_ALL_BOTS', true),
        ],

        /**
         * Алерты и уведомления
         */
        'alerts' => [
            'enabled' => env('TEGBOT_ALERTS_ENABLED', false),
            'email' => env('TEGBOT_ALERTS_EMAIL'),
            'telegram_chat_id' => env('TEGBOT_ALERTS_CHAT_ID'),
        ],

        /**
         * Метрики
         */
        'metrics' => [
            'enabled' => env('TEGBOT_METRICS_ENABLED', true),
            'retention_days' => env('TEGBOT_METRICS_RETENTION', 30),
            'track_per_bot' => env('TEGBOT_METRICS_PER_BOT', true),
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
        'enabled' => env('TEGBOT_EXPERIMENTAL', false),

        /**
         * Асинхронная обработка
         */
        'async_processing' => env('TEGBOT_ASYNC_PROCESSING', false),

        /**
         * AI интеграция
         */
        'ai_integration' => env('TEGBOT_AI_INTEGRATION', false),

        /**
         * Автоматическое обновление webhook'ов при изменении конфигурации
         */
        'auto_update_webhooks' => env('TEGBOT_AUTO_UPDATE_WEBHOOKS', false),
    ],
];
