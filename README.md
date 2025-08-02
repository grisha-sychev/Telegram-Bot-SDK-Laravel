# Telegram Bot SDK Laravel

Фреймворк для создания Telegram ботов на Laravel с поддержкой мультиботной архитектуры и разделения окружений.

## 🚀 Возможности

- **Мультиботная архитектура** - управление множественными ботами
- **Разделение окружений** - поддержка dev и prod токенов
- **Автоматическое создание классов** ботов
- **Webhook управление** с поддержкой SSL
- **Статистика и мониторинг** ботов
- **Экспорт/импорт** данных
- **Резервное копирование**

## 📋 Требования

- PHP 8.1+
- Laravel 10+
- MySQL/PostgreSQL/SQLite

## 🔧 Установка

1. Установите пакет:
```bash
composer require tbot/laravel
```

2. Опубликуйте конфигурацию:
```bash
php artisan vendor:publish --provider="Bot\Providers\BotServiceProvider" --tag="config"
```

3. Запустите миграции:
```bash
php artisan migrate
```

## 🌍 Настройка окружений

Система поддерживает разделение на dev и prod окружения. Текущее окружение определяется переменной `APP_ENV` в файле `.env`:

```env
APP_ENV=dev  # или prod
```

## 🤖 Создание бота

### Команда bot:new

Создает нового бота с поддержкой разделения токенов:

```bash
php artisan bot:new
```

Процесс создания:
1. **Ввод имени бота** (латинские буквы, без пробелов)
2. **Токен для разработки** (dev) - опционально
3. **Токен для продакшена** (prod) - опционально
4. **ID администраторов** - опционально
5. **Настройка webhook** - опционально

**Важно**: Хотя бы один токен (dev или prod) должен быть указан.

### Пример использования

```bash
# Создание бота с обоими токенами
php artisan bot:new

# Создание бота только с dev токеном
php artisan bot:new

# Создание бота с предустановленным webhook
php artisan bot:new --webhook=https://example.com/webhook/mybot
```

## 📊 Управление ботами

### Список ботов
```bash
php artisan bot:manage list
```

### Информация о боте
```bash
php artisan bot:manage show mybot
```

### Активация/деактивация
```bash
php artisan bot:manage enable mybot
php artisan bot:manage disable mybot
```

### Тестирование бота
```bash
php artisan bot:manage test mybot
```

### Удаление бота
```bash
php artisan bot:manage delete mybot
```

## 🌐 Управление Webhook

### Настройка webhook
```bash
php artisan bot:webhook set mybot https://example.com/webhook/mybot
```

### Информация о webhook
```bash
php artisan bot:webhook info mybot
```

### Удаление webhook
```bash
php artisan bot:webhook delete mybot
```

### Тестирование webhook
```bash
php artisan bot:webhook test mybot
```

## 📈 Статистика и мониторинг

### Общая статистика
```bash
php artisan bot:stats
```

### Статистика конкретного бота
```bash
php artisan bot:stats --bot=mybot
```

### Подробная статистика
```bash
php artisan bot:stats --bot=mybot --detailed
```

### Проверка здоровья системы
```bash
php artisan bot:health
```

## 🔄 Миграция данных

### Экспорт данных
```bash
php artisan bot:migrate export
```

### Импорт данных
```bash
php artisan bot:migrate import --path=backup.json
```

### Резервное копирование
```bash
php artisan bot:migrate backup
```

### Очистка данных
```bash
php artisan bot:migrate clear
```

## ⚙️ Конфигурация

### Просмотр конфигурации
```bash
php artisan bot:config show
```

### Получение значения
```bash
php artisan bot:config get api.timeout
```

### Установка значения
```bash
php artisan bot:config set api.timeout 30
```

## 🏗️ Структура проекта

```
app/
├── Bots/                    # Классы ботов
│   ├── AbstractBot.php     # Базовый класс
│   └── MyBot.php          # Ваш бот
├── Console/Commands/       # Команды управления
├── Models/
│   └── Bot.php            # Модель бота
config/
└── bot.php             # Конфигурация
```

## 🔐 Безопасность

- Токены автоматически маскируются при отображении
- Webhook secret генерируется автоматически
- Поддержка SSL сертификатов
- Валидация входящих запросов

## 📝 Создание класса бота

После создания бота автоматически создается класс в `app/Bots/`:

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
    }
}
```

## 🌍 Окружения

### Dev окружение
- Используется токен из поля `dev_token`
- Подходит для разработки и тестирования
- Может использовать HTTP webhook (локально)

### Prod окружение
- Используется токен из поля `prod_token`
- Для продакшена
- Требует HTTPS webhook

## 🔄 Миграция с версии 1.x

Если у вас есть существующие боты с единым токеном:

1. Запустите миграции:
```bash
php artisan migrate
```

2. Существующие токены автоматически перенесутся в поле `dev_token`

3. Добавьте prod токены через команду:
```bash
php artisan bot:manage show mybot
```

## 📞 Поддержка

<!-- - Документация: `vendor/bot/bot/docs/` -->
- Issues: GitHub Issues
- Обсуждения: GitHub Discussions

## 📄 Лицензия

MIT License 
