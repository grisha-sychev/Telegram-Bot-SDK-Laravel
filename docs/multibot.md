# 🤖 TegBot Multi-Bot System

## Обзор

TegBot теперь поддерживает мультиботную архитектуру, позволяющую управлять несколькими ботами из одного приложения Laravel. Каждый бот имеет свою конфигурацию, токен и webhook, но использует общую инфраструктуру.

## Ключевые возможности

- ✅ **Множественные боты**: Управление неограниченным количеством ботов
- ✅ **База данных**: Хранение конфигурации ботов в БД
- ✅ **Интерактивная настройка**: Простая команда `php artisan teg:set`
- ✅ **Автоматическое создание классов**: Генерация шаблонов ботов
- ✅ **Современные webhook'и**: Безопасные URL без токенов
- ✅ **Централизованное управление**: Команды для управления ботами
- ✅ **Обратная совместимость**: Поддержка старых конфигураций

## Быстрый старт

### 1. Запуск миграций

```bash
php artisan migrate
```

### 2. Добавление первого бота

```bash
php artisan teg:set
```

Команда запросит:
- **Имя бота** (латинские буквы, без пробелов)
- **Токен** (полученный от @BotFather)
- **ID администраторов** (опционально)

### 3. Просмотр ботов

```bash
php artisan teg:bot list
```

## Команды управления

### Добавление нового бота

```bash
php artisan teg:set
```

**Интерактивный процесс:**
1. Показывает существующие боты
2. Запрашивает имя нового бота
3. Проверяет уникальность имени
4. Запрашивает токен бота
5. Проверяет токен через Telegram API
6. Создает запись в базе данных
7. Генерирует класс бота
8. Настраивает webhook (опционально)

### Управление ботами

```bash
# Список всех ботов
php artisan teg:bot list

# Информация о конкретном боте
php artisan teg:bot show mybot

# Активация бота
php artisan teg:bot enable mybot

# Отключение бота
php artisan teg:bot disable mybot

# Удаление бота
php artisan teg:bot delete mybot

# Тестирование бота
php artisan teg:bot test mybot
```

## Структура базы данных

### Таблица `tegbot_bots`

```sql
CREATE TABLE tegbot_bots (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) UNIQUE,        -- Имя бота (mybot)
    token VARCHAR(255) UNIQUE,       -- Токен от BotFather
    username VARCHAR(255),           -- Username бота (@mybot)
    first_name VARCHAR(255),         -- Имя бота
    description TEXT,                -- Описание бота
    bot_id BIGINT UNIQUE,           -- ID бота в Telegram
    enabled BOOLEAN DEFAULT TRUE,    -- Активен ли бот
    webhook_url VARCHAR(255),        -- URL webhook
    webhook_secret VARCHAR(255),     -- Секрет webhook
    settings JSON,                   -- Дополнительные настройки
    admin_ids JSON,                  -- ID администраторов
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Структура классов ботов

### Автоматически генерируемый класс

```php
<?php

namespace App\Bots;

use Teg\LightBot;

class MybotBot extends LightBot
{
    public function commands(): void
    {
        $this->registerCommand('start', function () {
            $this->sendSelf('🎉 Привет! Я бот mybot');
        }, [
            'description' => 'Запуск бота'
        ]);

        $this->registerCommand('help', function () {
            $this->sendSelf('📋 Доступные команды:\n/start - Запуск бота\n/help - Помощь');
        }, [
            'description' => 'Помощь'
        ]);
    }

    public function fallback(): void
    {
        $this->sendSelf('❓ Неизвестная команда. Используйте /help для получения справки.');
    }
}
```

## Webhook маршруты

### Современный безопасный маршрут (рекомендуется)

```
POST /webhook/{botName}
```

**Пример:** `https://yourdomain.com/webhook/mybot`

**Преимущества:**
- Токен не передается в URL
- Более безопасно
- Легче логировать и отслеживать

### Классический маршрут (обратная совместимость)

```
POST /bot/{token}
```

**Пример:** `https://yourdomain.com/bot/123456789:AABBccDD...`

## Конфигурация

### Переменные окружения

```env
# Мультибот настройки
TEGBOT_MULTIBOT_ENABLED=true
TEGBOT_AUTO_CREATE_CLASSES=true
TEGBOT_MAX_BOTS=100
TEGBOT_AUTO_ENABLE_BOTS=true

# Webhook настройки
TEGBOT_AUTO_GENERATE_WEBHOOK_SECRET=true
TEGBOT_WEBHOOK_BASE_URL=https://yourdomain.com

# Логирование
TEGBOT_LOG_MULTIBOT=true
TEGBOT_STORE_COMMANDS_HISTORY=true

# Производительность
TEGBOT_CACHE_BOT_INFO=true
TEGBOT_CACHE_BOT_INFO_TTL=3600
```

### Конфигурация в config/tegbot.php

```php
'multibot' => [
    'enabled' => true,
    'auto_create_classes' => true,
    'bots_path' => 'App\\Bots',
    'bots_namespace' => 'App\\Bots',
    'max_bots' => 100,
    'auto_enable' => true,
],
```

## Примеры использования

### Создание бота магазина

```bash
php artisan teg:set
# Имя: shop
# Токен: 123456789:AABBccDD...
# Админы: 123456789
```

**Результат:**
- Файл: `app/Bots/ShopBot.php`
- Webhook: `https://yourdomain.com/webhook/shop`
- Database запись в `tegbot_bots`

### Создание новостного бота

```bash
php artisan teg:set
# Имя: news
# Токен: 987654321:XYZabc...
# Админы: 123456789,987654321
```

**Результат:**
- Файл: `app/Bots/NewsBot.php`
- Webhook: `https://yourdomain.com/webhook/news`

## Безопасность

### Webhook Secret

Каждый бот может иметь свой уникальный webhook secret:

```php
// Автоматически генерируется при создании
$bot->webhook_secret = Str::random(32);
```

### Администраторы

У каждого бота могут быть свои администраторы:

```php
$bot->admin_ids = [123456789, 987654321];
```

### IP ограничения

Глобальные IP ограничения применяются ко всем ботам:

```env
TEGBOT_ALLOWED_IPS=1.2.3.4,5.6.7.8
```

## Мониторинг

### Проверка всех ботов

```bash
php artisan teg:health
```

### Тестирование конкретного бота

```bash
php artisan teg:bot test mybot
```

### Метрики по ботам

```env
TEGBOT_METRICS_PER_BOT=true
```

## Миграция со старой системы

### Автоматическая миграция

Если у вас есть старая конфигурация в `config/tegbot.php`:

```php
// Старый формат
'token' => env('TEGBOT_TOKEN'),

// Новый формат поддерживает оба варианта
'token' => env('TEGBOT_TOKEN'), // для обратной совместимости
'multibot' => [
    'enabled' => true,
    // новые настройки
],
```

### Ручная миграция

1. Создайте бота через команду:
```bash
php artisan teg:set
```

2. Используйте существующий токен из `.env`

3. Обновите webhook в Telegram:
```bash
php artisan teg:webhook set https://yourdomain.com/webhook/mybot
```

## Troubleshooting

### Бот не отвечает

1. Проверьте статус бота:
```bash
php artisan teg:bot show mybot
```

2. Проверьте webhook:
```bash
php artisan teg:bot test mybot
```

3. Проверьте логи:
```bash
tail -f storage/logs/laravel.log | grep TegBot
```

### Класс бота не найден

1. Проверьте существование файла:
```bash
ls -la app/Bots/
```

2. Создайте класс заново:
```bash
# Отредактируйте SetupCommand для пересоздания класса
php artisan teg:set
```

### Проблемы с базой данных

1. Запустите миграции:
```bash
php artisan migrate
```

2. Проверьте структуру таблицы:
```bash
php artisan tinker
>>> Schema::hasTable('tegbot_bots')
>>> DB::table('tegbot_bots')->count()
```

## Лучшие практики

### Именование ботов

- Используйте короткие осмысленные имена
- Только латинские буквы и цифры
- Начинайте с буквы
- Примеры: `shop`, `news`, `support`, `analytics`

### Организация кода

```
app/Bots/
├── ShopBot.php          # Бот магазина
├── NewsBot.php          # Новостной бот
├── SupportBot.php       # Бот поддержки
└── Traits/
    ├── HasAdminCommands.php
    └── HasUserManagement.php
```

### Конфигурация по окружениям

```env
# Development
TEGBOT_DEBUG=true
TEGBOT_LOG_MULTIBOT=true
TEGBOT_AUTO_CREATE_CLASSES=true

# Production
TEGBOT_DEBUG=false
TEGBOT_CACHE_BOT_INFO=true
TEGBOT_AUTO_UPDATE_WEBHOOKS=false
```

## Примеры расширенной настройки

### Кастомные настройки бота

```php
$bot = Bot::create([
    'name' => 'advanced',
    'token' => $token,
    'settings' => [
        'language' => 'en',
        'timezone' => 'Europe/London',
        'features' => ['payments', 'inline_queries'],
        'rate_limit' => 10,
    ]
]);
```

### Использование настроек в боте

```php
class AdvancedBot extends LightBot
{
    private array $settings;

    public function setBotConfig(array $config): void
    {
        $this->settings = $config['settings'] ?? [];
    }

    public function commands(): void
    {
        $language = $this->settings['language'] ?? 'ru';
        
        $this->registerCommand('start', function () use ($language) {
            $message = $language === 'en' 
                ? '🎉 Hello! I am advanced bot'
                : '🎉 Привет! Я продвинутый бот';
            
            $this->sendSelf($message);
        });
    }
}
```

## API для разработчиков

### Получение бота в коде

```php
use App\Models\Bot;

// По имени
$bot = Bot::byName('mybot')->first();

// По токену
$bot = Bot::byToken($token)->first();

// Только активные боты
$activeBots = Bot::enabled()->get();
```

### Создание бота программно

```php
use App\Models\Bot;

$bot = Bot::create([
    'name' => 'automated',
    'token' => '123456789:AABBccDD...',
    'username' => 'automatedbot',
    'first_name' => 'Automated Bot',
    'bot_id' => 123456789,
    'enabled' => true,
    'admin_ids' => [987654321],
    'settings' => [
        'auto_respond' => true,
        'language' => 'ru'
    ]
]);
```

### События ботов

Вы можете слушать события создания/обновления ботов:

```php
// В EventServiceProvider
use App\Models\Bot;

Bot::created(function ($bot) {
    Log::info("New bot created: {$bot->name}");
    
    // Отправить уведомление админам
    // Создать директории для бота
    // Настроить мониторинг
});

Bot::updated(function ($bot) {
    if ($bot->wasChanged('enabled')) {
        $status = $bot->enabled ? 'enabled' : 'disabled';
        Log::info("Bot {$bot->name} was {$status}");
    }
});
```

## Заключение

Мультиботная система TegBot предоставляет мощный и гибкий способ управления несколькими Telegram ботами из одного Laravel приложения. Система обеспечивает:

- 🔧 **Простоту настройки** через интерактивные команды
- 🛡️ **Безопасность** с индивидуальными webhook secret
- 📊 **Мониторинг** каждого бота отдельно
- 🚀 **Масштабируемость** для любого количества ботов
- 🔄 **Обратную совместимость** со старой системой

Начните с команды `php artisan teg:set` и создайте своего первого бота за несколько минут! 