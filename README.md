# Telegram Bot SDK Laravel

Современный SDK для создания Telegram ботов на Laravel с поддержкой мультиботной архитектуры и простой изоляции окружений.

## 🚀 Возможности

- 🤖 **Мультиботная архитектура** - несколько ботов в одном приложении
- 🔒 **Простая изоляция окружений** - автоматическое добавление суффикса "dev" к webhook URL
- 🌍 **Поддержка доменов** - разные домены для dev и prod
- 🔐 **Безопасность** - валидация токенов и доменов
- 📊 **Мониторинг** - команды для проверки здоровья ботов
- 🎯 **Простота** - простой API для создания ботов

## 🔒 Изоляция ботов

### Проблема
У вас есть два бота с разными токенами (prod и dev), но они влияют друг на друга. Когда вы отправляете `/start` в prod боте, сообщение появляется в dev боте и наоборот.

### Решение
Простая система изоляции: для dev окружения автоматически добавляется суффикс "dev" к webhook URL.

#### Логика изоляции

1. **Prod окружение**: `/webhook/mybot`
2. **Dev окружение**: `/webhook/mybotdev`

Система автоматически определяет окружение по наличию суффикса "dev" в URL.

## 📦 Установка

```bash
composer require your-vendor/telegram-bot-sdk-laravel
```

## 🔧 Настройка

### 1. Публикация конфигурации

```bash
php artisan vendor:publish --tag=bot-config
```

### 2. Миграции

```bash
php artisan migrate
```

### 3. Создание бота

```bash
php artisan bot:setup mybot
```

## 🤖 Создание бота

После создания бота автоматически генерируется класс:

```php
<?php

namespace App\Bots;

use App\Models\Bot;

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
            $environment = Bot::getCurrentEnvironment();
            $this->sendSelf("🎉 Привет! Я бот MyBot (окружение: {$environment})");
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

## 🌍 Поддержка переводов

```php
<?php

namespace App\Bots;

class MyBot extends AbstractBot
{
    use I18nModule;

    public function commands(): void
    {
        $this->registerCommand('start', function () {
            $keyboard = [
                [$this->translate('messages.button.start')],
                [$this->translate('messages.button.help')]
            ];
            
            $this->sendSelf($this->translate('messages.welcome'), $keyboard);
        });
    }
}
```

### Файлы переводов

```php
// resources/lang/en/messages.php
return [
    'welcome' => 'Welcome to our bot!',
    'button' => [
        'start' => 'Start',
        'help' => 'Help',
    ],
];
```

## 🔍 Команды

### Настройка и управление

```bash
# Создание бота
php artisan bot:setup mybot

# Проверка здоровья
php artisan bot:health

# Статистика
php artisan bot:stats

# Конфигурация
php artisan bot:config show
```

### Webhook

```bash
# Установка webhook
php artisan bot:webhook set mybot

# Удаление webhook
php artisan bot:webhook delete mybot

# Информация о webhook
php artisan bot:webhook info mybot
```

## 🛡️ Безопасность

- 🔐 Автоматическая генерация webhook secrets
- 🛡️ Валидация токенов и доменов
- 🔒 Проверка SSL сертификатов
- 🚫 Защита от спама и rate limiting
- 👥 Система администраторов
- 🔒 Простая изоляция между dev и prod окружениями

## 📊 Мониторинг

```bash
# Проверка здоровья всех ботов
php artisan bot:health

# Статистика
php artisan bot:stats

# Конфигурация
php artisan bot:config show
```

## 📚 Документация

- [Использование переводов](I18N_USAGE.md)

## 🤝 Лицензия

MIT License 
