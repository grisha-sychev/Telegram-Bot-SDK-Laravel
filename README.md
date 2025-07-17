# TegBot - Продвинутый Laravel SDK для Telegram ботов

![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-4479A1.svg?style=for-the-badge&logo=mysql&logoColor=white) 
![Redis](https://img.shields.io/badge/redis-%23DD0031.svg?style=for-the-badge&logo=redis&logoColor=white)
![Telegram](https://img.shields.io/badge/Telegram-2CA5E0?style=for-the-badge&logo=telegram&logoColor=white)

![Packagist Version](https://img.shields.io/packagist/v/tegbot/tegbot)
![License](https://img.shields.io/packagist/l/tegbot/tegbot)
![Downloads](https://img.shields.io/packagist/dt/tegbot/tegbot)

**TegBot** - это мощный и безопасный Laravel пакет для создания Telegram ботов производственного уровня. Пакет предоставляет полный набор инструментов для разработки стабильных, масштабируемых и безопасных ботов.

## ⭐ Основные возможности

- 🛡️ **Безопасность**: Валидация webhook'ов, защита от спама, проверка входящих данных
- 📱 **Обработка медиа**: Полная поддержка фото, видео, документов, стикеров и аудио
- 🔧 **Middleware система**: Гибкая система промежуточных обработчиков
- 🎯 **Расширенные команды**: Поддержка аргументов, разрешений, описаний
- 📊 **Мониторинг**: Встроенные инструменты для отслеживания здоровья бота
- 🚀 **Производительность**: Retry механизм, rate limiting, кэширование
- 🔄 **Совместимость**: 100% обратная совместимость с существующими ботами

## 🚀 Быстрый старт

### Установка

```bash
composer require tegbot/tegbot
```

### Публикация конфигурации

```bash
php artisan vendor:publish --provider="Teg\Providers\TegbotServiceProvider"
```

### Настройка бота

```bash
php artisan teg:set
```

### Создание первого бота

```php
<?php

namespace App\Bots;

use Teg\Modules\UserModule;
use Teg\Modules\StateModule;

class MyBot extends AdstractBot
{
    use StateModule, UserModule;

    public function main(): void
    {
        // Регистрация команд с новой системой
        $this->registerCommand('start', function () {
            $this->start();
        }, [
            'description' => 'Запуск бота',
            'private_only' => true,
        ]);

        $this->registerCommand('help', function () {
            $this->sendSelf($this->generateHelp());
        }, [
            'description' => 'Показать справку',
        ]);

        // Обработка медиа с подписями
        $this->mediaWithCaption(function ($mediaInfo, $caption) {
            $this->handleMedia($mediaInfo, $caption);
        });

        // Автоматическая обработка команд
        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText);
        }
    }

    private function start()
    {
        $this->sendSelf('🚀 Добро пожаловать! Бот готов к работе.');
    }

    private function handleMedia($mediaInfo, $caption)
    {
        $type = $mediaInfo['type'];
        $this->sendSelf("📎 Получен файл типа: {$type}");
    }
}
```

## 📚 Документация

### Основные разделы

- [🔧 **Установка и настройка**](docs/installation.md)
- [🛡️ **Безопасность**](docs/security.md)
- [📱 **Обработка медиа**](docs/media.md)
- [🎯 **Система команд**](docs/commands.md)
- [🔄 **Middleware**](docs/middleware.md)
- [📊 **Мониторинг**](docs/monitoring.md)
- [⚙️ **Конфигурация**](docs/configuration.md)
- [🚀 **Примеры**](docs/examples.md)

### Новые возможности v2.0

#### 🛡️ Безопасность и валидация

```php
// Автоматическая валидация входящих данных
public function main(): void
{
    // safeMain() автоматически:
    // - Проверяет webhook данные
    // - Валидирует структуру сообщений
    // - Обрабатывает ошибки
    // - Логирует проблемы
}

// Проверка типа сообщения
if ($this->hasMessageText()) {
    // Обработка текстовых сообщений
}

if ($this->getMessageType() === 'photo') {
    // Обработка фото
}
```

#### 📱 Продвинутая обработка медиа

```php
// Получение информации о медиа файлах
$photoInfo = $this->getPhotoInfo();
$videoInfo = $this->getVideoInfo();
$documentInfo = $this->getDocumentInfo();

// Скачивание файлов
$filePath = $this->downloadFile($fileId, '/storage/downloads/');

// Обработка медиа с подписями
$this->mediaWithCaption(function ($mediaInfo, $caption) {
    $type = $mediaInfo['type']; // photo, video, document, etc.
    $data = $mediaInfo['data']; // размеры, длительность, имя файла
    
    // Ваша логика обработки
});
```

#### 🔧 Middleware система

```php
// Глобальные middleware
$this->globalMiddleware([
    'spam_protection', // встроенная защита от спама
    'activity_logging', // логирование активности
]);

// Middleware для конкретной команды
$this->registerCommand('admin', $callback, [
    'middleware' => [
        function ($bot, $parsed) {
            if (!$bot->isAdmin()) {
                $bot->sendSelf('🚫 Доступ запрещен');
                return false;
            }
            return true;
        }
    ],
]);
```

#### 🎯 Расширенная система команд

```php
// Команда с аргументами и ограничениями
$this->registerCommand('ban', function ($args) {
    $userId = $args[0] ?? null;
    $reason = $args[1] ?? 'Нарушение правил';
    // логика бана
}, [
    'description' => 'Заблокировать пользователя',
    'args' => ['user_id', 'reason?'],
    'admin_only' => true,
    'private_only' => true,
]);

// Автоматическая генерация справки
$helpText = $this->generateHelp();
```

## 📊 Мониторинг и диагностика

### Проверка здоровья бота

```bash
php artisan teg:health
```

Результат:
```
✅ Telegram API: Подключение успешно
✅ Конфигурация бота: Настроен корректно  
✅ Хранилище файлов: Доступно для записи
✅ Последняя активность: 2 минуты назад
⚠️  Последняя ошибка: 1 час назад (обработана)
```

### Логирование активности

```php
// Автоматическое логирование
$this->logActivity('command_executed', [
    'command' => '/start',
    'user_id' => $this->getUserId,
    'chat_type' => $this->getChatType(),
]);

// Логирование ошибок
$this->logError('API Error', $exception, [
    'method' => 'sendMessage',
    'user_id' => $this->getUserId,
]);
```

## 🔧 Настройка конфигурации

Файл `config/tegbot.php` содержит все настройки:

```php
return [
    'api' => [
        'base_url' => 'https://api.telegram.org',
        'timeout' => 30,
        'retries' => 3,
    ],
    'security' => [
        'webhook_secret' => env('TEGBOT_WEBHOOK_SECRET'),
        'admin_ids' => explode(',', env('TEGBOT_ADMIN_IDS', '')),
        'spam_protection' => [
            'enabled' => true,
            'max_messages_per_minute' => 20,
        ],
    ],
    'files' => [
        'download_path' => storage_path('app/tegbot/downloads'),
        'max_file_size' => 20 * 1024 * 1024, // 20MB
    ],
    'logging' => [
        'enabled' => true,
        'level' => 'info',
        'max_entries' => 1000,
    ],
];
```

## 🚀 Производственное использование

### Обязательные настройки для продакшена

1. **Webhook Secret**: Установите `TEGBOT_WEBHOOK_SECRET` в `.env`
2. **Admin IDs**: Укажите `TEGBOT_ADMIN_IDS` для административных команд
3. **Rate Limiting**: Настройте ограничения в конфигурации
4. **Логирование**: Включите логирование для мониторинга
5. **Мониторинг**: Настройте регулярные проверки здоровья

### Переменные окружения

```env
TEGBOT_TOKEN=your_bot_token_here
TEGBOT_WEBHOOK_SECRET=your_secret_key
TEGBOT_ADMIN_IDS=123456789,987654321
TEGBOT_DEBUG=false
TEGBOT_RATE_LIMIT=20
```

## 🔄 Миграция с предыдущих версий

Пакет полностью обратно совместим. Существующие боты будут работать без изменений.

Для использования новых возможностей:
1. Обновите конфигурацию: `php artisan vendor:publish --provider="Teg\Providers\TegbotServiceProvider" --force`
2. Замените `main()` на `safeMain()` в маршрутах (опционально)
3. Добавьте новые команды через `registerCommand()`

## 📝 Примеры использования

Полные примеры доступны в папке [docs/examples/](docs/examples/).

## 🤝 Поддержка

- 📧 **Email**: support@tegbot.ru
- 💬 **Telegram**: @tegbot_support
- 🐛 **Issues**: [GitHub Issues](https://github.com/tegbot/tegbot/issues)
- 📖 **Wiki**: [GitHub Wiki](https://github.com/tegbot/tegbot/wiki)

## 📄 Лицензия

MIT License. Подробности в файле [LICENSE](LICENSE).

## 🎉 Авторы

Создано командой TegBot для Laravel сообщества.

---

**TegBot v2.0** - От прототипа к производству 🚀
