# 🔧 Установка и настройка TegBot

## Системные требования

- **PHP**: 8.1 или выше
- **Laravel**: 10.0 или выше  
- **Extensions**: cURL, JSON, OpenSSL
- **Memory**: Минимум 128MB для PHP
- **Диск**: 50MB свободного места

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
- Миграции для базы данных (если нужно)

## Шаг 3: Настройка переменных окружения

Добавьте в `.env`:

```env
# Основные настройки
TEGBOT_TOKEN=your_bot_token_here
TEGBOT_WEBHOOK_SECRET=your_random_secret_key

# Администраторы (через запятую)
TEGBOT_ADMIN_IDS=123456789,987654321

# Дополнительные настройки
TEGBOT_DEBUG=false
TEGBOT_RATE_LIMIT=20
TEGBOT_LOG_LEVEL=info
```

### Получение токена бота

1. Откройте Telegram и найдите [@BotFather](https://t.me/botfather)
2. Отправьте команду `/newbot`
3. Следуйте инструкциям для создания бота
4. Скопируйте полученный токен в `.env`

### Генерация webhook secret

```bash
php artisan tinker
>>> Str::random(32)
=> "your_generated_secret_key"
```

## Шаг 4: Настройка бота

```bash
php artisan teg:set
```

Команда поможет:
- Создать первого бота
- Настроить webhook
- Проверить соединение с Telegram API

## Шаг 5: Создание первого бота

Создайте файл `app/Bots/MyBot.php`:

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
        $this->registerCommand('start', function () {
            $this->start();
        }, [
            'description' => 'Запуск бота',
        ]);

        // Автоматическая обработка команд
        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText);
        }
    }

    private function start()
    {
        $this->sendSelf('🚀 Добро пожаловать в мой бот!');
    }
}
```

## Шаг 6: Настройка маршрутов

В `routes/tegbot.php` (или создайте этот файл):

```php
<?php

use App\Bots\MyBot;
use Illuminate\Support\Facades\Route;

Route::post('/telegram/webhook', function () {
    $bot = new MyBot();
    return $bot->safeMain(); // Используем безопасный метод
});
```

## Шаг 7: Настройка веб-сервера

### Nginx

```nginx
location /telegram {
    try_files $uri $uri/ /index.php?$query_string;
    
    # Ограничение размера файлов (для медиа)
    client_max_body_size 20M;
    
    # Таймауты для длительных операций
    proxy_read_timeout 30s;
    proxy_connect_timeout 5s;
}
```

### Apache

```apache
<Location "/telegram">
    # Увеличиваем лимиты для медиа файлов
    LimitRequestBody 20971520
    
    # Таймауты
    ProxyTimeout 30
</Location>
```

## Шаг 8: Установка webhook

```bash
# Автоматическая установка через artisan
php artisan teg:webhook:set https://yourdomain.com/telegram/webhook

# Или вручную через curl
curl -X POST "https://api.telegram.org/bot{YOUR_BOT_TOKEN}/setWebhook" \
     -H "Content-Type: application/json" \
     -d '{"url":"https://yourdomain.com/telegram/webhook","secret_token":"your_webhook_secret"}'
```

## Шаг 9: Проверка установки

```bash
# Проверка здоровья бота
php artisan teg:health

# Проверка webhook
php artisan teg:webhook:info

# Отправка тестового сообщения
php artisan teg:test
```

## Настройка базы данных (опционально)

Если ваш бот использует базу данных:

```bash
# Запуск миграций
php artisan migrate

# Заполнение тестовыми данными
php artisan db:seed --class=TegbotSeeder
```

## Настройка для разработки

### Локальное тестирование с ngrok

```bash
# Установка ngrok
npm install -g ngrok

# Запуск туннеля
ngrok http 8000

# Установка webhook на ngrok URL
php artisan teg:webhook:set https://your-ngrok-url.ngrok.io/telegram/webhook
```

### Отладка

В `.env` для разработки:

```env
TEGBOT_DEBUG=true
TEGBOT_LOG_LEVEL=debug
LOG_LEVEL=debug
```

## Настройка для продакшена

### Обязательные настройки

1. **HTTPS**: Webhook должен работать только по HTTPS
2. **SSL сертификат**: Валидный SSL сертификат обязателен
3. **Firewall**: Ограничьте доступ к webhook только для Telegram IP
4. **Мониторинг**: Настройте логирование и мониторинг
5. **Backup**: Регулярное резервное копирование

### Оптимизация производительности

```php
// config/tegbot.php
return [
    'cache' => [
        'enabled' => true,
        'driver' => 'redis', // или 'memcached'
        'ttl' => 3600,
    ],
    'queue' => [
        'enabled' => true,
        'connection' => 'redis',
        'queue' => 'tegbot',
    ],
];
```

### Настройка очередей

```bash
# Запуск воркера очередей
php artisan queue:work --queue=tegbot

# Настройка Supervisor для автозапуска
sudo supervisorctl start laravel-worker:*
```

## Решение проблем

### Проблема: Webhook не отвечает

```bash
# Проверка доступности
curl -I https://yourdomain.com/telegram/webhook

# Проверка логов
tail -f storage/logs/laravel.log
```

### Проблема: Telegram API недоступен

```bash
# Проверка соединения
php artisan teg:health

# Тест API
curl -X GET "https://api.telegram.org/bot{YOUR_BOT_TOKEN}/getMe"
```

### Проблема: Ошибки прав доступа

```bash
# Исправление прав для папки storage
chmod -R 775 storage/
chown -R www-data:www-data storage/

# Права для папки загрузок
mkdir -p storage/app/tegbot/downloads
chmod -R 775 storage/app/tegbot/
```

## Следующие шаги

После успешной установки изучите:

- [🛡️ Безопасность](security.md)
- [📱 Обработка медиа](media.md)
- [🎯 Система команд](commands.md)
- [🔄 Middleware](middleware.md)

## Полезные команды

```bash
# Статус бота
php artisan teg:status

# Очистка кэша
php artisan teg:cache:clear

# Просмотр конфигурации
php artisan teg:config

# Экспорт настроек
php artisan teg:export-config
```

---

✅ **Установка завершена!** Ваш бот готов к работе. 