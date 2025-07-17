# 🤖 TegBot - Laravel пакет для создания Telegram ботов

> Laravel пакет для создания множества Telegram ботов в одном приложении

[![Latest Version](https://img.shields.io/packagist/v/tegbot/tegbot)](https://packagist.org/packages/tegbot/tegbot)
[![Laravel](https://img.shields.io/badge/Laravel-10%2B-FF2D20)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

## Возможности

- 🤖 **Мультиботная архитектура** - множество ботов в одном Laravel приложении
- 🗄️ **База данных** - конфигурация ботов хранится в БД
- 🛠️ **CLI управление** - создание и управление ботами через artisan команды
- 🔧 **Автогенерация** - автоматическое создание классов ботов
- 📊 **Мониторинг** - проверка здоровья и статистика ботов
- 🌐 **Webhook** - современная система webhook'ов

## Системные требования

- PHP 8.1+
- Laravel 10.0+
- MySQL/PostgreSQL/SQLite
- cURL, JSON, OpenSSL

## Установка

### 1. Установка пакета

```bash
composer require tegbot/tegbot
```

### 2. Публикация конфигурации

```bash
php artisan vendor:publish --provider="Teg\Providers\TegbotServiceProvider"
```

### 3. Настройка .env

```env
TEGBOT_DEBUG=false
TEGBOT_WEBHOOK_SECRET=your_random_secret_key
TEGBOT_MULTIBOT_ENABLED=true
TEGBOT_AUTO_CREATE_CLASSES=true
TEGBOT_WEBHOOK_BASE_URL=https://yourdomain.com
```

### 4. Запуск миграций

```bash
php artisan migrate
```

## Создание первого бота

### Интерактивное создание

```bash
php artisan teg:set
```

или

```bash
# Создание бота интерактивным вариантом
php artisan teg:set
```

Команда поможет:
- Добавить нового бота в систему
- Автоматически создать класс бота
- Настроить webhook
- Проверить соединение с Telegram API

### Пример процесса создания

```
🚀 TegBot Multi-Bot Setup Wizard

➕ Добавление нового бота

Введите имя бота (латинские буквы, без пробелов): myshop
Введите токен бота (полученный от @BotFather): 123456789:AABBccDDeeFFggHHii...
Введите ID администраторов (через запятую): 123456789

🤖 Информация о боте:
  📝 Имя: My Shop Bot
  🆔 Username: @myshopbot
  📡 ID: 123456789

✅ Бот сохранен в базу данных
📝 Создание класса бота MyshopBot...
✅ Класс бота создан: app/Bots/MyshopBot.php
🌐 Настройка webhook...
✅ Webhook установлен: https://yourdomain.com/webhook/myshop
```

## Создание простого бота

После выполнения `php artisan teg:set` создается класс бота:

```php
<?php
// app/Bots/MyshopBot.php

namespace App\Bots;

use Teg\LightBot;

class MyshopBot extends LightBot
{
    public function main(): void
    {
        $this->commands();
        
        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText);
        } elseif ($this->hasCallbackQuery()) {
            $this->handleCallbacks();
        } else {
            $this->fallback();
        }
    }

    public function commands(): void
    {
        $this->registerCommand('start', function () {
            $this->sendSelf('🛍️ Добро пожаловать в наш магазин!');
        }, ['description' => 'Запуск бота']);

        $this->registerCommand('help', function () {
            $this->sendSelf('📱 Доступные команды:' . "\n" .
                            '/start - Запуск бота' . "\n" .
                            '/catalog - Каталог товаров' . "\n" .
                            '/help - Помощь');
        }, ['description' => 'Помощь']);

        $this->registerCommand('catalog', function () {
            $keyboard = [
                [['text' => 'Товар 1', 'callback_data' => 'product_1']],
                [['text' => 'Товар 2', 'callback_data' => 'product_2']]
            ];
            
            $this->sendMessage($this->getChatId, '📱 Каталог товаров:', [
                'reply_markup' => ['inline_keyboard' => $keyboard]
            ]);
        }, ['description' => 'Каталог товаров']);
    }

    public function handleCallbacks(): void
    {
        $data = $this->getCallbackData;
        
        if (strpos($data, 'product_') === 0) {
            $productId = str_replace('product_', '', $data);
            $this->sendSelf("Вы выбрали товар ID: {$productId}");
            $this->answerCallbackQuery('Товар добавлен в корзину!');
        }
    }

    public function fallback(): void
    {
        $this->sendSelf('❓ Неизвестная команда. Используйте /help для просмотра доступных команд.');
    }
}
```

## Управление ботами

### Список ботов

```bash
php artisan teg:bot list
```

### Информация о боте

```bash
php artisan teg:bot show myshop
```

### Включение/отключение бота

```bash
php artisan teg:bot enable myshop
php artisan teg:bot disable myshop
```

### Тестирование бота

```bash
php artisan teg:bot test myshop
```

## Мониторинг

### Проверка здоровья системы

```bash
php artisan teg:health
```

### Статистика ботов

```bash
php artisan teg:stats
```

## Маршрутизация

TegBot автоматически создает маршруты в `routes/tegbot.php`:

```php
// routes/tegbot.php
Route::post('/webhook/{botName}', function ($botName) {
    // Автоматическая обработка webhook'ов
});
```

Webhook URL для каждого бота: `https://yourdomain.com/webhook/{bot_name}`

## Основные методы LightBot

```php
// Отправка сообщений
$this->sendSelf('Текст сообщения');
$this->sendMessage($chatId, 'Текст', $options);

// Получение данных
$this->getUserId;
$this->getChatId;
$this->getMessageText;
$this->getCallbackData;

// Проверки
$this->hasMessageText();
$this->isMessageCommand();
$this->hasCallbackQuery();

// Команды и callback
$this->registerCommand($command, $callback, $options);
$this->handleCommand($text);
$this->answerCallbackQuery($text);
```

## Документация

Подробная документация доступна в папке `docs/`:

- [📘 Установка](docs/installation.md)
- [⚙️ Конфигурация](docs/configuration.md)
- [🎯 Команды](docs/commands.md)
- [🤖 Мультиботы](docs/multibot.md)
- [💡 Примеры](docs/examples.md)
- [🛡️ Безопасность](docs/security.md)
- [🔄 Middleware](docs/middleware.md)
- [📊 Мониторинг](docs/monitoring.md)
- [📱 Медиа](docs/media.md)

## Важные изменения

⚠️ **TegBot v2.0 НЕ совместим с v0.3.x** из-за кардинальных архитектурных изменений:

- Конфигурация ботов теперь в базе данных, а не в .env
- Новая система классов и наследования
- Другая структура webhook'ов
- Новые artisan команды

## Поддержка

- **GitHub**: [Issues](https://github.com/tegbot/tegbot/issues)
- **Telegram**: [@tegbot_support](https://t.me/tegbot_support)

## Лицензия

MIT License. Подробности в файле [LICENSE](LICENSE).

---

**TegBot v2.0** - Создавайте мощных Telegram ботов для Laravel! 🚀 