# 📚 TegBot v2.x - Документация

Добро пожаловать в полную документацию **TegBot v2.x** - самого продвинутого Laravel SDK для создания Telegram ботов!

## 🚀 О TegBot v2.x

TegBot v2.x - это кардинальное обновление популярного пакета для создания Telegram ботов в Laravel. Мы полностью переработали архитектуру, добавили множество новых функций и превратили простой пакет в полноценную платформу для создания ботов производственного уровня.

### 🎯 Что нового в v2.x

- 🛡️ **Безопасность**: Многоуровневая система защиты
- 📱 **Медиа**: Полная поддержка всех типов файлов
- 🔧 **Middleware**: Гибкая система промежуточных обработчиков
- 🎯 **Команды**: Расширенная система с аргументами и разрешениями
- 📊 **Мониторинг**: Встроенные инструменты диагностики
- 🚀 **Производительность**: Retry механизм, rate limiting, кэширование

## 📖 Структура документации

### 🏁 Начало работы

| Раздел                                           | Описание                           | Статус   |
| ------------------------------------------------ | ---------------------------------- | -------- |
| [🔧 Установка и настройка](docs/installation.md) | Пошаговое руководство по установке | ✅ Готово |
| [⚙️ Конфигурация](docs/configuration.md)         | Настройка всех параметров пакета   | ✅ Готово |
|                                                  |                                    |          |

### 🎯 Основные функции

| Раздел                                | Описание                       | Статус   |
| ------------------------------------- | ------------------------------ | -------- |
| [🛡️ Безопасность](docs/security.md)  | Защита и валидация данных      | ✅ Готово |
| [📱 Обработка медиа](docs/media.md)   | Работа с файлами всех типов    | ✅ Готово |
| [🎯 Система команд](docs/commands.md) | Регистрация и обработка команд | ✅ Готово |
| [🔄 Middleware](docs/middleware.md)   | Промежуточные обработчики      | ✅ Готово |

### 📊 Мониторинг и диагностика

| Раздел                              | Описание                    | Статус   |
| ----------------------------------- | --------------------------- | -------- |
| [📊 Мониторинг](docs/monitoring.md) | Отслеживание состояния бота | ✅ Готово |

### 💡 Практика

| Раздел                                       | Описание                          | Статус   |
| -------------------------------------------- | --------------------------------- | -------- |
| [🚀 Примеры использования](docs/examples.md) | Готовые боты для разных сценариев | ✅ Готово |

## 🎯 Быстрый старт

### 1. Установка

```bash
composer require tegbot/tegbot
```

### 2. Публикация конфигурации

```bash
php artisan vendor:publish --provider="Teg\Providers\TegbotServiceProvider"
```

### 3. Настройка

```env
TEGBOT_TOKEN=your_bot_token_here
TEGBOT_WEBHOOK_SECRET=your_random_secret
TEGBOT_ADMIN_IDS=123456789
```

### 4. Создание бота

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
        // Регистрация команд
        $this->registerCommand('start', function () {
            $this->sendSelf('🚀 Привет! Я готов к работе!');
        }, [
            'description' => 'Запуск бота',
        ]);

        // Автоматическая обработка команд
        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText);
        }
    }
}
```

### 5. Настройка маршрута

```php
// routes/tegbot.php
Route::post('/telegram/webhook', function () {
    $bot = new \App\Bots\MyBot();
    return $bot->safeMain(); // Безопасная обработка
});
```

## 🆕 Новые возможности v2.x

### 🛡️ Автоматическая безопасность

```php
// Валидация происходит автоматически
public function main(): void
{
    // safeMain() проверяет:
    // ✅ Подлинность webhook
    // ✅ Структуру данных  
    // ✅ Защиту от спама
    // ✅ Rate limiting
}
```

### 📱 Умная обработка медиа

```php
// Обработка любых типов файлов
$this->mediaWithCaption(function ($mediaInfo, $caption) {
    $type = $mediaInfo['type']; // photo, video, document, etc.
    $data = $mediaInfo['data']; // размеры, длительность, etc.
    
    // Скачивание файла
    $filePath = $this->downloadFile($mediaInfo['file_id']);
});
```

### 🎯 Продвинутые команды

```php
// Команда с аргументами и ограничениями
$this->registerCommand('ban', function ($args) {
    $userId = $args[0] ?? null;
    $reason = $args[1] ?? 'Нарушение';
    // логика бана
}, [
    'description' => 'Заблокировать пользователя',
    'args' => ['user_id', 'reason?'],
    'admin_only' => true,
    'rate_limit' => 5,
]);
```

### 🔧 Мощные middleware

```php
// Глобальные middleware для всех сообщений
$this->globalMiddleware([
    'spam_protection',
    'activity_logging',
    'user_validation',
]);

// Middleware для конкретных команд
$this->registerCommand('sensitive', $callback, [
    'middleware' => [
        function ($bot, $parsed) {
            return $bot->checkSpecialPermissions();
        }
    ],
]);
```

## 🔍 Диагностика и мониторинг

### Проверка здоровья

```bash
php artisan teg:health
```

Результат:
```
✅ Telegram API: Подключение успешно (150ms)
✅ Конфигурация: Настроена корректно
✅ Webhook: Активен и работает
✅ Безопасность: Все проверки пройдены
⚠️  Предупреждения: Высокая нагрузка на API (85%)
```

### Статистика

```bash
php artisan teg:stats
```

## 🔄 Миграция с v1.x

TegBot v2.x полностью обратно совместим! Ваши существующие боты продолжат работать без изменений.

### Для использования новых функций:

1. Обновите конфигурацию:
```bash
php artisan vendor:publish --provider="Teg\Providers\TegbotServiceProvider" --force
```

2. Замените `main()` на `safeMain()` в маршрутах (опционально)

3. Начните использовать новые возможности постепенно

## 📊 Сравнение версий

| Функция | v1.x | v2.x |
|---------|------|------|
| Безопасность | ❌ Базовая | ✅ Многоуровневая |
| Обработка медиа | ⚠️ Ограниченная | ✅ Полная |
| Команды | ⚠️ Простые | ✅ Расширенные |
| Middleware | ❌ Нет | ✅ Есть |
| Мониторинг | ❌ Нет | ✅ Встроенный |
| Производительность | ⚠️ Базовая | ✅ Оптимизированная |
| Совместимость | - | ✅ 100% |

## 💼 Примеры использования

### 🛒 E-commerce бот
Полнофункциональный интернет-магазин с каталогом, корзиной и заказами
- [Просмотреть код](docs/examples.md#e-commerce-бот)

### 📰 Новостной бот  
Система подписок и рассылки новостей
- [Просмотреть код](docs/examples.md#новостной-бот)

### 🎫 Служба поддержки
Система тикетов и обработки обращений
- [Просмотреть код](docs/examples.md#служба-поддержки)

## 🤝 Поддержка и сообщество

### 📧 Контакты

- **Email**: support@tegbot.ru
- **Telegram**: [@tegbot_support](https://t.me/tegbot_support)
- **GitHub**: [Issues](https://github.com/grisha-sychev/Telegram-Bot-SDK-Laravel/issues)

### 📖 Дополнительные ресурсы

- **Wiki**: [GitHub Wiki](https://github.com/tegbot/tegbot/wiki)
- **Примеры**: [GitHub Examples](https://github.com/tegbot/examples)
- **Блог**: [tegbot.ru/blog](https://tegbot.ru/blog)

### 🔄 Обновления

Следите за обновлениями:
- ⭐ Поставьте звезду на [GitHub](https://github.com/tegbot/tegbot)
- 📢 Подпишитесь на канал [@tegbot_news](https://t.me/tegbot_news)
- 📝 Читайте [changelog](CHANGELOG.md)

## 📄 Лицензия

MIT License. Подробности в файле [LICENSE](../LICENSE).

## 🎉 Благодарности

Спасибо всем разработчикам и сообществу Laravel за вдохновение и поддержку!

---

**TegBot v2.x** - Превращаем идеи в работающих ботов! 🚀

*Документация обновлена: {{ date('d.m.Y') }}* 
