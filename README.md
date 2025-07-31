# 🤖 Laravel пакет для Telegram ботов

Создавайте профессиональных Telegram ботов в Laravel за минуты! Это пакет с мультиботной архитектурой, безопасностью и всеми возможностями Telegram Bot API.

## ✨ Основные возможности

- 🚀 **Мультиботная архитектура** - управляйте десятками ботов из одного приложения
- 🛡️ **Встроенная безопасность** - анти-спам, валидация webhook'ов, права доступа
- 📱 **Полная поддержка медиа** - фото, видео, документы, стикеры, голосовые
- 🎯 **Умные команды** - аргументы, middleware, права администратора
- 💾 **Управление состоянием** - сохранение контекста диалогов
- 🌐 **Автоперевод** - интеграция с Google Translate
- ⚡ **Простая установка** - готов к работе за 2 минуты

## 🚀 Быстрый старт

### 1. Установка

```bash
composer require tbot/laravel
```

### 2. Публикация файлов

```bash
php artisan vendor:publish --provider="Teg\Providers\TegbotServiceProvider"
```

### 3. Миграции

```bash
php artisan migrate
```

### 4. Создание первого бота

```bash
php artisan bot:new
```

Команда проведет вас через интерактивную настройку - просто введите токен от @BotFather!

### 5. Ваш первый бот готов! 🎉

```php
<?php

namespace App\Bots;

class MyBot extends AbstractBot
{
 public function main(): void
    {
        $this->commands();
        // Обрабатываем команды
        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText());
        }


        // Не обязательно, но рекомендуется, так как обработка автоматическая, будет просто игнорироваться
        $this->fallback(function () {
            $this->sendSelf('❌ Ошибка'); // Или что то другое, на ваше усмотрение
        });
    }

    public function commands(): void
    {
        // Регистрируем команды, description не обязательно, но рекомендуется
        $this->registerCommand('start', function () {
            $this->sendSelf('🎉 Привет! Я бот {$botName}');
        }, [
            'description' => 'Запуск бота'
        ]);

        $this->registerCommand('help', function () {
            $this->sendSelf([
                '📋 Доступные команды:', 
                '', 
                '/start - Запуск бота', 
                '/help - Помощь'
                ]
            );
        }, [
            'description' => 'Помощь'
        ]);
    }
}
```

## 📋 Примеры использования

### Команды с аргументами и правами

```php
$this->registerCommand('ban', function ($args) {
    $userId = $args[0] ?? null;
    $reason = $args[1] ?? 'Нарушение правил';
    
    $this->sendSelf("Пользователь {$userId} забанен. Причина: {$reason}");
}, [
    'description' => 'Забанить пользователя',
    'args' => ['user_id', 'reason?'],
    'admin_only' => true
]);
```

### Обработка медиафайлов

```php
use App\Enums\MediaType;

// Обработка фото
$this->media(MediaType::photo, function () {
    $photoInfo = $this->getPhotoInfo();
    $this->sendSelf('Красивое фото! Размер: ' . $photoInfo['largest']['file_size']);
});

// Обработка документов
$this->media(MediaType::document, function () {
    $docInfo = $this->getDocumentInfo();
    $filePath = $this->saveFile($docInfo['file_id']);
    $this->sendSelf('Документ сохранен: ' . $filePath);
});
```

### Управление состоянием (диалоги)

```php
// Запоминаем состояние
$this->setState('waiting_name');

// Проверяем состояние в другом сообщении  
$this->clue('waiting_name', function () {
    $name = $this->getMessageText();
    $this->sendSelf("Привет, {$name}!");
    $this->deleteState(); // Очищаем состояние
});
```

### Клавиатуры и кнопки

```php
// Обычная клавиатура
$this->sendSelf('Выбери опцию:', [
    ['🏠 Главная', '📊 Статистика'],
    ['⚙️ Настройки', '❓ Помощь']
]);

// Инлайн клавиатура
$this->sendSelfInline('Что делаем?', [
    ['confirm', '✅ Подтвердить'], 
    ['cancel', '❌ Отменить'],
    ['edit', '📝 Редактировать']
]);

// Обработка нажатий
$this->callback('confirm', function () {
    $this->sendSelf('Подтверждено!');
});

$this->callback('cancel', function () {
    $this->sendSelf('Отменено');
});
```

### Работа с пользователями

```php
// Получить текущего пользователя
$user = $this->getCurrentUser();
echo $user->display_name; // @username или имя

// Проверить премиум
if ($this->isUserPremium()) {
    $this->sendSelf('💎 У вас премиум аккаунт!');
}

// Получить язык пользователя
$lang = $this->getUserLanguage(); // ru, en, etc.
```

## 🛠️ Управление ботами

### Проверка здоровья ботов

```bash
php artisan teg:health
```

Результат:
```
✅ Telegram API: Подключение успешно (150ms)
✅ Конфигурация: Настроена корректно  
✅ Webhook: Активен и работает
✅ База данных: Соединение установлено
```

### Статистика ботов

```bash
php artisan bot:stats
```

## ⚙️ Конфигурация

Файл `config/bot.php` содержит все настройки:

```php
return [
    // Мультибот
    'multibot' => [
        'enabled' => true,
        'max_bots' => 100,
    ],
    
    // Безопасность
    'security' => [
        'spam_protection' => true,
        'admin_ids' => [123456789], // Ваш Telegram ID
    ],
    
    // Файлы
    'files' => [
        'max_file_size' => 50 * 1024 * 1024, // 50MB
        'allowed_types' => ['jpg', 'png', 'pdf', 'doc'],
    ],
];
```

## 🎯 Расширенные возможности

### Middleware для команд

```php
$this->registerCommand('admin', function () {
    $this->sendSelf('Админ панель');
}, [
    'middleware' => [
        function ($bot, $parsed) {
            if (!$bot->isAdmin()) {
                $bot->sendSelf('❌ Нет доступа');
                return false; // Блокируем выполнение
            }
            return true;
        }
    ]
]);
```

### Автоперевод сообщений

```php
// Включить модуль перевода
use Teg\Modules\TranslateModule;

class MyBot extends AbstractBot
{
    use TranslateModule;
    
    public function main(): void
    {
        // Сообщения автоматически переводятся на язык пользователя
        $this->sendSelf($this->trans('Добро пожаловать!'));
    }
}
```

### Обработка ошибок

```php
$this->fail(function () {
    $this->sendSelf('🤔 Не понял команду. Используй /help');
});
```

## 📊 Мониторинг

TegBot автоматически логирует:
- ✅ Входящие сообщения
- ✅ Выполненные команды  
- ✅ Ошибки API
- ✅ Действия пользователей

## 🔒 Безопасность

- **Валидация webhook'ов** - проверка подлинности запросов
- **Анти-спам защита** - лимиты на количество сообщений
- **Права доступа** - команды только для админов
- **Фильтрация медиа** - контроль типов и размеров файлов

## 📁 Структура проекта

```
app/
├── Bots/           # Ваши боты
│   └── MyBot.php
├── Models/         # Модели пакета
│   ├── Bot.php     # Модель бота
│   └── UserTelegram.php
config/
└── tegbot.php      # Конфигурация
routes/
└── tegbot.php      # Webhook маршруты
```

## 🔗 Webhook маршруты

Автоматически создается маршрут для каждого бота:
```
POST /webhook/{botName}
```

Пример:
```
POST /webhook/mybot → App\Bots\MybotBot
```

## 💡 Полезные команды

```bash
# Создать нового бота
php artisan teg:set

# Проверить состояние
php artisan teg:health  

# Очистить состояния пользователей
php artisan teg:cleanup

# Просмотр конфигурации
php artisan teg:config
```

## 📦 API методы

Полная поддержка Telegram Bot API:

```php
// Отправка сообщений
$this->sendSelf('Текст');
$this->sendSelfPhoto($photo, 'Подпись');
$this->sendSelfVideo($video);

// Редактирование
$this->editSelf($messageId, 'Новый текст');
$this->deleteSelf($messageId);

// Получение данных
$userId = $this->getUserId();
$messageText = $this->getMessageText();
$photoId = $this->getPhotoId();
```

## 🏗️ Архитектура

- **LightBot** - базовый класс всех ботов
- **Modules** - модульная система (State, User, Translate)
- **API** - обертка над Telegram Bot API
- **Storage** - системы хранения состояний (SQL/Redis)

## 📋 Требования

- PHP 8.1+
- Laravel 10+
- MySQL/PostgreSQL (для мультибота)

## 📄 Лицензия

MIT License - используйте свободно в коммерческих проектах.

---

**TegBot** - превращаем идеи в работающих ботов за минуты! 🚀

💬 **Поддержка**: [Telegram](https://t.me/tegbot_support)  
📚 **Документация**: [`docs/`](docs/)  
🐛 **Баги**: [GitHub Issues](https://github.com/tegbot/tegbot/issues) 
