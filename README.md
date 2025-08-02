# Telegram Bot SDK Laravel

Фреймворк для создания Telegram ботов на Laravel с поддержкой мультибота и разделением окружений.

## Возможности

- 🤖 **Мультиботная архитектура** - управление множественными ботами
- 🔄 **Разделение окружений** - отдельные токены и домены для dev/prod
- 🌍 **i18n поддержка** - встроенная система интернационализации без зависимостей
- 🛠️ **Интерактивные команды** - удобное управление через CLI
- 🔐 **Безопасность** - webhook secrets, валидация токенов
- 📊 **Мониторинг** - проверка здоровья ботов, метрики
- 🗄️ **База данных** - хранение настроек и состояний
- 🚀 **Готовые команды** - быстрая настройка и развертывание

## Установка

```bash
composer require tbot/laravel
```
```bash
php artisan vendor:publish --provider="Bot\Providers\BotServiceProvider"
```
```bash
php artisan migrate
```




## Быстрый старт

### 1. Создание бота

```bash
php artisan bot:new
```

Команда проведет вас через интерактивную настройку:
- Ввод имени бота
- Настройка токенов для dev и prod окружений
- Настройка доменов для dev и prod окружений
- Указание администраторов
- Настройка webhook

### 2. Управление ботами

```bash
# Список всех ботов
php artisan bot:manage list

# Информация о конкретном боте
php artisan bot:manage show mybot

# Активация/деактивация бота
php artisan bot:manage enable mybot
php artisan bot:manage disable mybot

# Тестирование бота
php artisan bot:manage test mybot
```

### 3. Управление доменами

```bash
# Установка домена для окружения
php artisan bot:domain set mybot dev https://dev.example.com
php artisan bot:domain set mybot prod https://example.com

# Просмотр доменов бота
php artisan bot:domain show mybot

# Список всех доменов
php artisan bot:domain list
```

### 4. Управление webhook

```bash
# Настройка webhook (автоматически использует домен из БД)
php artisan bot:webhook set mybot

# Информация о webhook
php artisan bot:webhook info mybot

# Удаление webhook
php artisan bot:webhook delete mybot

# Тестирование webhook
php artisan bot:webhook test mybot
```

## Разделение окружений

Фреймворк поддерживает разделение токенов и доменов по окружениям:

### Токены
- `dev_token` - токен для разработки
- `prod_token` - токен для продакшена

### Домены
- `dev_domain` - домен для разработки (например: https://dev.example.com)
- `prod_domain` - домен для продакшена (например: https://example.com)

### Автоматическое определение окружения

Система автоматически определяет текущее окружение через `APP_ENV`:
- `APP_ENV=dev` - использует dev_token и dev_domain
- `APP_ENV=prod` - использует prod_token и prod_domain

### Примеры использования

```php
// В коде бота
$bot = Bot::find(1);

// Получение токена для текущего окружения
$token = $bot->getTokenAttribute();

// Получение домена для текущего окружения
$domain = $bot->getDomainAttribute();

// Получение для конкретного окружения
$devToken = $bot->getTokenForEnvironment('dev');
$prodDomain = $bot->getDomainForEnvironment('prod');
```

## Структура проекта

```
app/
├── Bots/                    # Классы ботов
│   ├── AbstractBot.php     # Базовый класс бота
│   └── MyBot.php          # Ваш бот
├── Console/Commands/       # CLI команды
│   ├── BotCommand.php     # Управление ботами
│   ├── SetupCommand.php   # Настройка ботов
│   ├── WebhookCommand.php # Управление webhook
│   └── DomainCommand.php  # Управление доменами
└── Models/
    └── Bot.php            # Модель бота

config/
└── bot.php               # Конфигурация

database/migrations/      # Миграции БД
```

## Конфигурация

Основные настройки в `config/bot.php`:

```php
return [
    'multibot' => [
        'enabled' => true,
        'auto_create_classes' => true,
        'bots_path' => 'App\\Bots',
    ],
    
    'webhook' => [
        'base_url' => env('BOT_WEBHOOK_BASE_URL', env('APP_URL')),
        'auto_generate_secret' => true,
    ],
    
    'security' => [
        'webhook_secret' => env('BOT_WEBHOOK_SECRET'),
        'admin_ids' => array_filter(explode(',', env('BOT_ADMIN_IDS', ''))),
    ],
];
```

## Команды

### Основные команды

| Команда | Описание |
|---------|----------|
| `bot:new` | Создание нового бота |
| `bot:manage list` | Список всех ботов |
| `bot:manage show {bot}` | Информация о боте |
| `bot:manage test {bot}` | Тестирование бота |
| `bot:webhook set {bot}` | Настройка webhook |
| `bot:domain set {bot} {env} {domain}` | Установка домена |

### Управление доменами

| Команда | Описание |
|---------|----------|
| `bot:domain set {bot} {env} {domain}` | Установка домена |
| `bot:domain show {bot}` | Просмотр доменов бота |
| `bot:domain list` | Список всех доменов |

### Управление webhook

| Команда | Описание |
|---------|----------|
| `bot:webhook set {bot} {url?}` | Настройка webhook |
| `bot:webhook info {bot}` | Информация о webhook |
| `bot:webhook delete {bot}` | Удаление webhook |
| `bot:webhook test {bot}` | Тестирование webhook |

## Интернационализация (i18n)

Фреймворк включает встроенную систему интернационализации без внешних зависимостей:

### Использование модуля

Добавьте I18nModule в ваш бот:

```php
use Bot\Modules\I18nModule;

class MyBot extends LightBot
{
    use I18nModule;
    
    public function start()
    {
        // Автоматический перевод в sendSelf/sendOut
        $this->sendSelf('messages.welcome', [
            ['messages.button.start'],
            ['messages.button.help']
        ]);
    }
}
```

### Ручной перевод

```php
// Перевод с параметрами
$greeting = $this->translate('messages.user.greeting', ['name' => 'John']);

// Перевод массивов
$buttons = $this->translateArray([
    ['messages.button.start'],
    ['messages.button.help']
]);
```

### Файлы переводов

Создайте файлы переводов в `resources/lang/{locale}/`:

```php
// resources/lang/en/messages.php
return [
    'welcome' => 'Welcome to our bot!',
    'button' => [
        'start' => 'Start',
        'help' => 'Help',
    ],
    'user' => [
        'greeting' => 'Hello, :name!',
    ],
];
```

Подробная документация: [I18N_USAGE.md](I18N_USAGE.md)

## Создание бота

После создания бота автоматически генерируется класс:

```php
<?php

namespace App\Bots;

class MyBot extends AbstractBot
{
    public function main(): void
    {
        $this->commands();
        
        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText());
        }
    }

    public function commands(): void
    {
        $this->registerCommand('start', function () {
            $this->sendSelf('🎉 Привет! Я бот MyBot');
        }, [
            'description' => 'Запуск бота'
        ]);

        $this->registerCommand('help', function () {
            $this->sendSelf([
                '📋 Доступные команды:', 
                '', 
                '/start - Запуск бота', 
                '/help - Помощь'
            ]);
        }, [
            'description' => 'Помощь'
        ]);
    }
}
```

## Безопасность

- 🔐 Автоматическая генерация webhook secrets
- 🛡️ Валидация токенов и доменов
- 🔒 Проверка SSL сертификатов
- 🚫 Защита от спама и rate limiting
- 👥 Система администраторов

## Мониторинг

```bash
# Проверка здоровья всех ботов
php artisan bot:health

# Статистика
php artisan bot:stats

# Конфигурация
php artisan bot:config show
```

## Лицензия

MIT License 
