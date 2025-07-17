# 🔧 Установка и настройка TegBot v2.0

## Системные требования

- **PHP**: 8.1 или выше
- **Laravel**: 10.0 или выше  
- **Extensions**: cURL, JSON, OpenSSL
- **Memory**: Минимум 128MB для PHP
- **База данных**: MySQL 5.7+, PostgreSQL 10+ или SQLite 3.8+
- **Диск**: 100MB свободного места

## Шаг 1: Установка пакета

```bash
composer require tegbot/tegbot
```

## Шаг 2: Публикация конфигурации

```bash
php artisan vendor:publish --provider="Teg\Providers\TegbotServiceProvider"
```

Эта команда создаст:
- `config/tegbot.php` - основная конфигурация
- `app/Bots/` - папка для ваших ботов
- `routes/tegbot.php` - маршруты для webhook'ов
- Миграции для базы данных

## Шаг 3: Настройка переменных окружения

Добавьте в `.env`:

```env
# Настройки TegBot (базовые)
TEGBOT_DEBUG=false
TEGBOT_WEBHOOK_SECRET=your_random_secret_key

# Мультибот настройки
TEGBOT_MULTIBOT_ENABLED=true
TEGBOT_AUTO_CREATE_CLASSES=true
TEGBOT_MAX_BOTS=100

# Webhook настройки
TEGBOT_WEBHOOK_BASE_URL=https://yourdomain.com
TEGBOT_AUTO_GENERATE_WEBHOOK_SECRET=true

# Безопасность
TEGBOT_RATE_LIMIT=20
TEGBOT_LOG_LEVEL=info
```

### Генерация webhook secret

```bash
php artisan tinker
>>> Str::random(32)
=> "your_generated_secret_key"
```

## Шаг 4: Запуск миграций

**⚠️ ВАЖНО:** В TegBot v2.0 токены и настройки ботов хранятся в базе данных!

```bash
php artisan migrate
```

Это создаст таблицу `tegbot_bots` для хранения конфигурации ваших ботов.

## Шаг 5: Добавление первого бота

```bash
php artisan teg:set
```

Интерактивная команда поможет:
- Добавить нового бота в систему
- Автоматически создать класс бота
- Настроить webhook
- Проверить соединение с Telegram API

### Пример процесса добавления бота:

```
🚀 TegBot Multi-Bot Setup Wizard

➕ Добавление нового бота

Введите имя бота (латинские буквы, без пробелов): myshop
Введите токен бота (полученный от @BotFather): 123456789:AABBccDDeeFFggHHiiJJkkLLmmNNooP
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
✅ Настройка TegBot завершена!
```

## Шаг 6: Структура автоматически созданного бота

Команда `teg:set` автоматически создаст файл `app/Bots/MyshopBot.php`:

```php
<?php

namespace App\Bots;

use Teg\LightBot;

class MyshopBot extends LightBot
{
    public function main(): void
    {
        // Регистрируем команды
        $this->commands();
        
        // Обрабатываем входящие сообщения
        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText);
        } else {
            // Если это не команда, вызываем fallback
            $this->fallback();
        }
    }

    public function commands(): void
    {
        $this->registerCommand('start', function () {
            $this->sendSelf('🎉 Привет! Я бот myshop');
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

## Шаг 7: Маршруты webhook'ов (уже настроены)

Файл `routes/tegbot.php` уже содержит необходимые маршруты:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Models\Bot;

// Современный безопасный маршрут для мультиботов
Route::post('/webhook/{botName}', function ($botName) {
    // Загружаем бота из базы данных
    $botModel = Bot::byName($botName)->where('enabled', true)->first();
    
    if (!$botModel) {
        return response()->json(['error' => 'Bot not found'], 404);
    }

    // Создаем экземпляр класса бота
    $class = $botModel->getBotClass();
    $bot = new $class();
    
    // Устанавливаем конфигурацию
    $bot->setToken($botModel->token);
    $bot->setBotName($botName);
    
    // Запускаем обработку
    return response()->json(['ok' => true]);
});

// Классический маршрут (обратная совместимость)
Route::post('/bot/{token}', function ($token) {
    $botModel = Bot::byToken($token)->where('enabled', true)->first();
    
    if (!$botModel) {
        return response()->json(['error' => 'Bot not found'], 404);
    }
    
    // Аналогичная логика...
});
```

**Рекомендуется использовать современный маршрут `/webhook/{botName}`!**

## Шаг 8: Настройка веб-сервера

### Nginx

```nginx
# Для всех webhook'ов TegBot
location /webhook/ {
    try_files $uri $uri/ /index.php?$query_string;
    
    # Ограничение размера файлов (для медиа)
    client_max_body_size 20M;
    
    # Таймауты для длительных операций
    proxy_read_timeout 30s;
    proxy_connect_timeout 5s;
    
    # Ограничение доступа только для Telegram IP
    allow 149.154.160.0/20;
    allow 91.108.4.0/22;
    deny all;
}
```

### Apache

```apache
<LocationMatch "^/webhook/">
    # Увеличиваем лимиты для медиа файлов
    LimitRequestBody 20971520
    
    # Таймауты
    ProxyTimeout 30
    
    # Ограничение доступа
    Require ip 149.154.160.0/20
    Require ip 91.108.4.0/22
</LocationMatch>
```

## Шаг 9: Проверка установки

```bash
# Проверка здоровья всех ботов
php artisan teg:health

# Список всех ботов
php artisan teg:bot list

# Информация о конкретном боте
php artisan teg:bot show myshop

# Тестирование бота
php artisan teg:bot test myshop
```

### Пример вывода проверки здоровья:

```
🔍 Проверка состояния мультиботной системы TegBot

✅ База данных: Подключение успешно
✅ Таблица ботов: Найдена (3 бота)
✅ Конфигурация: Настроена корректно

🤖 Проверка ботов:
  ✅ myshop (@myshopbot): Активен, webhook работает
  ✅ news (@newsbot): Активен, webhook работает  
  ❌ support (@supportbot): Отключен

📊 Статистика:
  • Всего ботов: 3
  • Активных: 2
  • Отключенных: 1
```

## Управление ботами

### Основные команды

```bash
# Добавление нового бота
php artisan teg:set

# Список всех ботов
php artisan teg:bot list

# Информация о боте
php artisan teg:bot show myshop

# Активация бота
php artisan teg:bot enable myshop

# Отключение бота
php artisan teg:bot disable myshop

# Удаление бота
php artisan teg:bot delete myshop

# Тестирование бота
php artisan teg:bot test myshop
```

### Управление webhook'ами

```bash
# Просмотр информации о webhook конкретного бота
php artisan teg:webhook info myshop

# Установка webhook для бота
php artisan teg:webhook set myshop https://yourdomain.com/webhook/myshop

# Удаление webhook
php artisan teg:webhook delete myshop
```

## Настройка для разработки

### Локальное тестирование с ngrok

```bash
# Установка ngrok
npm install -g ngrok

# Запуск туннеля
ngrok http 8000

# Установка webhook на ngrok URL для конкретного бота
php artisan teg:webhook set myshop https://your-ngrok-url.ngrok.io/webhook/myshop
```

### Отладка

В `.env` для разработки:

```env
TEGBOT_DEBUG=true
TEGBOT_LOG_LEVEL=debug
TEGBOT_LOG_MULTIBOT=true
LOG_LEVEL=debug
```

## Настройка для продакшена

### Обязательные настройки

1. **HTTPS**: Webhook должен работать только по HTTPS
2. **SSL сертификат**: Валидный SSL сертификат обязателен  
3. **База данных**: Настройка индексов для производительности
4. **Мониторинг**: Регулярные проверки здоровья ботов
5. **Backup**: Резервное копирование базы данных ботов

### Оптимизация производительности

```env
# Кэширование информации о ботах
TEGBOT_CACHE_BOT_INFO=true
TEGBOT_CACHE_BOT_INFO_TTL=3600

# Производительность
TEGBOT_AUTO_UPDATE_WEBHOOKS=false
TEGBOT_STORE_COMMANDS_HISTORY=false
```

### Мониторинг ботов

```bash
# Настройка автоматической проверки (в cron)
# Каждые 5 минут
*/5 * * * * cd /path/to/your/project && php artisan teg:health --no-interaction

# Ежедневная статистика
0 9 * * * cd /path/to/your/project && php artisan teg:stats --period=24h
```

## Миграция с TegBot v1.x

⚠️ **ВНИМАНИЕ: TegBot v2.0 НЕ имеет обратной совместимости!**

### Что изменилось:

1. **Токены**: Больше не хранятся в `.env`, только в базе данных
2. **Маршруты**: `/webhook/{botName}` вместо одного маршрута
3. **Конфигурация**: Через команды artisan, а не файлы
4. **Классы ботов**: Автоматическая генерация

### Процесс миграции:

1. Обновите пакет: `composer update tegbot/tegbot`
2. Запустите миграции: `php artisan migrate`
3. Добавьте ваши боты заново: `php artisan teg:set`
4. Перенесите логику из старых классов в новые
5. Обновите webhook'и

## Решение проблем

### Проблема: "Таблица tegbot_bots не найдена"

```bash
# Запуск миграций
php artisan migrate

# Если миграции не найдены
php artisan vendor:publish --provider="Teg\Providers\TegbotServiceProvider" --force
php artisan migrate
```

### Проблема: Webhook не отвечает

```bash
# Проверка конкретного бота
php artisan teg:bot test myshop

# Проверка доступности webhook
curl -I https://yourdomain.com/webhook/myshop

# Проверка логов
tail -f storage/logs/laravel.log | grep TegBot
```

### Проблема: Бот не найден

```bash
# Убедитесь что бот активен
php artisan teg:bot show myshop

# Активируйте бота если нужно
php artisan teg:bot enable myshop

# Проверьте что класс бота существует
ls -la app/Bots/
```

## Следующие шаги

После успешной установки изучите:

- [🤖 Мультиботная система](multibot.md) - подробное описание новой архитектуры
- [⚙️ Конфигурация](configuration.md) - настройка системы
- [🎯 Система команд](commands.md) - управление ботами
- [🛡️ Безопасность](security.md) - защита ботов
- [📱 Обработка медиа](media.md) - работа с файлами

## Полезные команды

```bash
# Состояние всех ботов
php artisan teg:health

# Статистика системы  
php artisan teg:stats

# Управление конфигурацией
php artisan teg:config show

# Резервное копирование ботов
php artisan teg:migrate backup

# Очистка логов
php artisan teg:logs clear
```

---

✅ **Установка TegBot v2.0 завершена!** Ваша мультиботная система готова к работе. 