# 🤖 TegBot v2.0 - Мультиботная архитектура

## Обзор

**TegBot v2.0** основан на революционной мультиботной архитектуре, позволяющей управлять множеством Telegram ботов из одного Laravel приложения. Это не дополнительная функция, а основа всей системы v2.0.

⚠️ **ВАЖНО:** TegBot v2.0 НЕ совместим с v1.x. Это полностью новая архитектура.

## Ключевые революционные изменения

- 🗄️ **База данных как источник истины**: Вся конфигурация ботов хранится в БД
- 🚫 **Конец .env токенов**: Токены больше не хранятся в файлах конфигурации
- 🎯 **Один проект = множество ботов**: Неограниченное количество ботов в одном приложении
- 🔐 **Индивидуальная безопасность**: Каждый бот имеет свои настройки безопасности
- 🌐 **Современные webhook**: URL вида `/webhook/{botName}` вместо `/bot/{token}`
- 🎛️ **Централизованное управление**: Полный контроль через artisan команды
- 📊 **Раздельный мониторинг**: Диагностика и метрики для каждого бота

## Архитектурные принципы

### 1. База данных как единый источник истины

```sql
-- Центральная таблица всех ботов
CREATE TABLE tegbot_bots (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) UNIQUE,        -- Уникальное имя бота
    token VARCHAR(255) UNIQUE,       -- Токен от BotFather
    username VARCHAR(255),           -- Username (@mybotname)
    first_name VARCHAR(255),         -- Отображаемое имя
    description TEXT,                -- Описание назначения
    bot_id BIGINT UNIQUE,           -- ID в Telegram
    enabled BOOLEAN DEFAULT TRUE,    -- Статус активности
    webhook_url VARCHAR(255),        -- URL для webhook
    webhook_secret VARCHAR(255),     -- Индивидуальный секрет
    settings JSON,                   -- Персональные настройки
    admin_ids JSON,                  -- Администраторы бота
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 2. Автоматическая генерация классов

Каждый бот автоматически получает свой класс:

```php
<?php
// app/Bots/ShopBot.php - автоматически создается
namespace App\Bots;

use Teg\LightBot;

class ShopBot extends LightBot
{
    public function main(): void
    {
        $this->commands();
        
        if ($this->hasMessageText() && $this->isMessageCommand()) {
            $this->handleCommand($this->getMessageText);
        } else {
            $this->fallback();
        }
    }

    public function commands(): void
    {
        $this->registerCommand('start', function () {
            $this->sendSelf('🛍️ Добро пожаловать в магазин!');
        }, ['description' => 'Запуск бота']);

        $this->registerCommand('catalog', function () {
            $this->showCatalog();
        }, ['description' => 'Показать каталог товаров']);
    }

    public function fallback(): void
    {
        $this->sendSelf('❓ Неизвестная команда. Используйте /help.');
    }
}
```

### 3. Современная маршрутизация

```php
// routes/tegbot.php - автоматически создается
Route::post('/webhook/{botName}', function ($botName) {
    // Загрузка бота из базы данных
    $botModel = Bot::byName($botName)->where('enabled', true)->first();
    
    if (!$botModel) {
        return response()->json(['error' => 'Bot not found'], 404);
    }

    // Создание экземпляра класса
    $class = $botModel->getBotClass(); // App\Bots\ShopBot
    $bot = new $class();
    
    // Инициализация с данными из БД
    $bot->setToken($botModel->token);
    $bot->setBotName($botName);
    $bot->setBotModel($botModel);
    
    // Обработка запроса
    return response()->json(['ok' => true]);
});
```

## Быстрый старт с нуля

### Шаг 1: Установка TegBot v2.0

```bash
# Установка пакета
composer require tegbot/tegbot

# Публикация конфигурации и маршрутов
php artisan vendor:publish --provider="Teg\Providers\TegbotServiceProvider"

# Создание таблиц базы данных
php artisan migrate
```

### Шаг 2: Настройка окружения

```env
# .env - НЕ СОДЕРЖИТ ТОКЕНОВ БОТОВ!
TEGBOT_MULTIBOT_ENABLED=true
TEGBOT_AUTO_CREATE_CLASSES=true
TEGBOT_WEBHOOK_BASE_URL=https://yourdomain.com
TEGBOT_WEBHOOK_SECRET=your_global_secret_32_chars
```

### Шаг 3: Добавление первого бота

```bash
php artisan teg:set
```

**Интерактивный процесс добавления:**

```
🚀 TegBot v2.0 Multi-Bot Setup Wizard

📋 Существующие боты: (пусто)

➕ Добавление нового бота

Введите имя бота (латинские буквы, без пробелов): shop
Введите токен бота (полученный от @BotFather): 123456789:AABBccDDeeFFggHHiiJJ...
Введите ID администраторов (через запятую): 123456789

🔍 Проверка токена через Telegram API...
✅ Токен валиден

🤖 Информация о боте:
  📝 Имя: My Shop Bot  
  🆔 Username: @myshopbot
  📡 ID: 123456789
  📄 Описание: (можно добавить позже)

✅ Сохранение в базу данных...
✅ Создание класса: app/Bots/ShopBot.php
🌐 Настройка webhook: https://yourdomain.com/webhook/shop
✅ Установка webhook в Telegram

🎉 Бот 'shop' успешно добавлен и готов к работе!

📋 Следующие шаги:
  • Отредактируйте app/Bots/ShopBot.php для добавления логики
  • Проверьте работу: php artisan teg:bot test shop
  • Посмотрите статус: php artisan teg:health
```

### Шаг 4: Проверка работы

```bash
# Проверка состояния системы
php artisan teg:health

# Список всех ботов
php artisan teg:bot list

# Тестирование конкретного бота
php artisan teg:bot test shop
```

## Полное управление через команды

### Базовые операции с ботами

```bash
# Создание нового бота
php artisan teg:set

# Просмотр списка
php artisan teg:bot list --format=table

# Детальная информация
php artisan teg:bot show shop

# Управление состоянием
php artisan teg:bot enable shop     # Активация
php artisan teg:bot disable shop    # Деактивация
php artisan teg:bot delete shop     # Удаление (с подтверждением)

# Тестирование и диагностика
php artisan teg:bot test shop       # Полная проверка бота
php artisan teg:health --bot=shop   # Здоровье конкретного бота
```

### Управление настройками ботов

```bash
# Просмотр настроек бота
php artisan teg:bot show shop

# Изменение настроек
php artisan teg:bot config shop --setting=language --value=en
php artisan teg:bot config shop --setting=timezone --value="Europe/London"
php artisan teg:bot config shop --setting=features --value='["payments","inline"]'

# Управление администраторами
php artisan teg:bot admin shop --add=987654321
php artisan teg:bot admin shop --remove=111222333
php artisan teg:bot admin shop --list
```

### Webhook операции

```bash
# Информация о webhook
php artisan teg:webhook info shop

# Установка/обновление webhook  
php artisan teg:webhook set shop
php artisan teg:webhook set shop https://custom-domain.com/webhook/shop

# Удаление webhook
php artisan teg:webhook delete shop

# Тестирование webhook
php artisan teg:webhook test shop
```

## Расширенные сценарии использования

### E-commerce экосистема

```bash
# Создание набора ботов для интернет-магазина
php artisan teg:set  # shop - основной магазин
php artisan teg:set  # support - служба поддержки  
php artisan teg:set  # analytics - аналитика для админов
php artisan teg:set  # notifications - уведомления о заказах
```

**Результат:**
```
app/Bots/
├── ShopBot.php          # Каталог, заказы, платежи
├── SupportBot.php       # Тикеты, FAQ, связь с операторами  
├── AnalyticsBot.php     # Статистика продаж, отчеты
└── NotificationsBot.php # Уведомления о новых заказах
```

**Webhook endpoints:**
```
https://yourdomain.com/webhook/shop
https://yourdomain.com/webhook/support  
https://yourdomain.com/webhook/analytics
https://yourdomain.com/webhook/notifications
```

### Медиа-платформа

```bash
# Создание ботов для новостного проекта
php artisan teg:set  # news_ru - русские новости
php artisan teg:set  # news_en - английские новости
php artisan teg:set  # breaking - экстренные новости
php artisan teg:set  # weather - погодный бот
```

### Корпоративная система

```bash
# Боты для компании
php artisan teg:set  # hr - HR отдел, вакансии
php artisan teg:set  # it_support - техподдержка
php artisan teg:set  # announcements - объявления
php artisan teg:set  # booking - бронирование переговорок
```

## Мониторинг и диагностика

### Системная диагностика

```bash
php artisan teg:health
```

**Подробный вывод для мультиботной системы:**

```
🔍 TegBot v2.0 Multi-Bot System Health Check

✅ Системные компоненты:
  ✅ PHP 8.2.0 (requirement: 8.1+)
  ✅ Laravel 10.48.4 (requirement: 10.0+) 
  ✅ Database: MySQL 8.0.32 (15ms connection)
  ✅ Redis: 6.2.6 (2ms connection)
  ✅ Storage: 15.2GB / 50GB (30% used)
  ✅ Memory: 127MB / 2GB (6% used)

✅ TegBot Infrastructure:
  ✅ Multibot system: Enabled
  ✅ Bot storage table: tegbot_bots (found)
  ✅ Auto class creation: Enabled
  ✅ Webhook base URL: https://yourdomain.com
  ✅ Global webhook secret: Configured (32 chars)

🤖 Bot Registry (4 active, 1 disabled):

  ✅ shop (@myshopbot) - E-commerce
    ├─ Status: 🟢 Active
    ├─ Class: ✅ App\Bots\ShopBot
    ├─ Telegram API: ✅ Valid (142ms)
    ├─ Webhook: ✅ https://yourdomain.com/webhook/shop  
    ├─ Secret: ✅ Individual webhook secret
    ├─ Admins: 👥 2 configured
    └─ Settings: 📋 5 custom parameters

  ✅ support (@supportbot) - Customer Service  
    ├─ Status: 🟢 Active
    ├─ Class: ✅ App\Bots\SupportBot
    ├─ Telegram API: ✅ Valid (156ms)
    ├─ Webhook: ✅ https://yourdomain.com/webhook/support
    ├─ Secret: ✅ Individual webhook secret  
    ├─ Admins: 👥 3 configured
    └─ Settings: 📋 7 custom parameters

  ✅ analytics (@analyticsbot) - Business Intelligence
    ├─ Status: 🟢 Active  
    ├─ Class: ✅ App\Bots\AnalyticsBot
    ├─ Telegram API: ✅ Valid (134ms)
    ├─ Webhook: ✅ https://yourdomain.com/webhook/analytics
    ├─ Secret: ✅ Individual webhook secret
    ├─ Admins: 👥 1 configured (admins only)
    └─ Settings: 📋 3 custom parameters

  ✅ notifications (@notifybot) - Order Alerts
    ├─ Status: 🟢 Active
    ├─ Class: ✅ App\Bots\NotificationsBot  
    ├─ Telegram API: ✅ Valid (128ms)
    ├─ Webhook: ✅ https://yourdomain.com/webhook/notifications
    ├─ Secret: ✅ Individual webhook secret
    ├─ Admins: 👥 2 configured
    └─ Settings: 📋 4 custom parameters

  ⚠️  weather (@weatherbot) - Weather Service
    ├─ Status: 🔴 Disabled (maintenance)
    ├─ Class: ✅ App\Bots\WeatherBot
    ├─ Telegram API: ⏸️ Skipped (bot disabled)
    ├─ Webhook: ❌ Not configured  
    ├─ Secret: ✅ Individual webhook secret
    ├─ Admins: 👥 1 configured
    └─ Settings: 📋 2 custom parameters

📊 Performance Metrics (last 24h):
  • Total messages processed: 15,247
  • Average response time: 145ms
  • Success rate: 99.2%
  • Peak concurrent users: 342
  • Webhook delivery success: 99.8%

🔧 Recommendations:
  ⚠️  Enable weather bot for full service coverage
  💡 Consider Redis cache for better performance (file cache detected)
  📈 Queue system recommended for high load (disabled)

🎯 Overall Status: ✅ HEALTHY (4/5 bots operational)
```

### Статистика по ботам

```bash
php artisan teg:stats --period=24h --detailed
```

```
📊 TegBot v2.0 Multi-Bot Statistics (24h)

🏆 Top Performing Bots:
┌─────────────┬───────────┬────────┬─────────┬──────────┬─────────────┐
│ Bot         │ Messages  │ Users  │ Commands│ Errors   │ Avg Response│
├─────────────┼───────────┼────────┼─────────┼──────────┼─────────────┤
│ shop        │ 8,453     │ 1,247  │ 2,156   │ 12 (0.1%)│ 142ms       │
│ support     │ 4,892     │ 523    │ 891     │ 3 (0.1%) │ 156ms       │
│ analytics   │ 1,567     │ 23     │ 445     │ 0 (0%)   │ 89ms        │
│ notifications│ 335      │ 89     │ 0       │ 1 (0.3%) │ 67ms        │
│ weather     │ 0 (disabled)│ 0     │ 0       │ 0        │ -           │
└─────────────┴───────────┴────────┴─────────┴──────────┴─────────────┘

💬 Most Popular Commands:
1. /start (shop) - 1,847 calls
2. /catalog (shop) - 892 calls  
3. /help (support) - 456 calls
4. /order (shop) - 334 calls
5. /status (support) - 289 calls

👥 User Distribution:
• Total unique users: 1,882
• New users today: 156
• Returning users: 1,726  
• Multi-bot users: 342 (18%)

🚨 Error Analysis:
• API timeouts: 8 cases (resolved)
• Invalid commands: 6 cases
• Webhook delivery failures: 2 cases  
• Rate limit hits: 1 case
```

## Продвинутые возможности

### Динамические настройки ботов

```php
// Изменение настроек бота во время выполнения
$bot = Bot::byName('shop')->first();

$bot->update([
    'settings' => array_merge($bot->settings ?? [], [
        'maintenance_mode' => true,
        'maintenance_message' => 'Магазин временно закрыт на техобслуживание',
        'estimated_downtime' => '2024-12-16 02:00:00'
    ])
]);
```

### Интеграция с событиями

```php
// app/Providers/EventServiceProvider.php
use App\Models\Bot;

Bot::created(function ($bot) {
    // Автоматическая настройка при создании нового бота
    Log::info("New bot created: {$bot->name}");
    
    // Создание директорий для бота
    Storage::makeDirectory("bots/{$bot->name}");
    
    // Уведомление администраторов
    Notification::send(
        User::whereIn('id', $bot->admin_ids)->get(),
        new BotCreatedNotification($bot)
    );
});

Bot::updated(function ($bot) {
    if ($bot->wasChanged('enabled')) {
        $status = $bot->enabled ? 'enabled' : 'disabled';
        Log::info("Bot {$bot->name} was {$status}");
        
        // Обновление webhook при изменении статуса
        if ($bot->enabled) {
            $bot->setupWebhook();
        } else {
            $bot->removeWebhook();
        }
    }
});
```

### Программное управление ботами

```php
// Создание бота программно
use App\Models\Bot;

$bot = Bot::create([
    'name' => 'automated_news',
    'token' => '123456789:AABBccDDeeFFggHH...',
    'username' => 'autonewsbot',
    'first_name' => 'Automated News Bot',
    'bot_id' => 123456789,
    'enabled' => true,
    'admin_ids' => [987654321],
    'settings' => [
        'auto_post' => true,
        'post_interval' => 3600, // каждый час
        'categories' => ['tech', 'business'],
        'language' => 'ru',
        'timezone' => 'Europe/Moscow'
    ]
]);

// Автоматическое создание класса
$bot->createBotClass();

// Установка webhook
$bot->setupWebhook("https://yourdomain.com/webhook/{$bot->name}");
```

## Безопасность мультиботной системы

### Индивидуальные webhook секреты

```php
// Каждый бот имеет свой уникальный секрет
$bot = Bot::find(1);
echo $bot->webhook_secret; // "sf8h3jk9dmq2kl5nv7x1c4p6..."

$anotherBot = Bot::find(2);  
echo $anotherBot->webhook_secret; // "k9m2x5n8q1v4c7p0sf3h6jl9..."
```

### Изоляция конфигураций

```php
// Настройки одного бота не влияют на другие
$shopBot = Bot::byName('shop')->first();
$shopBot->settings = [
    'payment_enabled' => true,
    'max_order_amount' => 100000
];

$supportBot = Bot::byName('support')->first();  
$supportBot->settings = [
    'working_hours' => ['9:00', '18:00'],
    'auto_assign' => true
];
```

### Раздельные права администраторов

```php
// У каждого бота свои администраторы
$shopBot->admin_ids = [123456789, 987654321];    // владелец + менеджер
$supportBot->admin_ids = [987654321, 555666777]; // менеджер + оператор
$analyticsBot->admin_ids = [123456789];          // только владелец
```

## Миграция и резервное копирование

### Полный экспорт всех ботов

```bash
php artisan teg:migrate export
```

```
💾 TegBot v2.0 Multi-Bot Export

📋 Collecting bot data...
  ✅ shop: Configuration, settings, admins
  ✅ support: Configuration, settings, admins  
  ✅ analytics: Configuration, settings, admins
  ✅ notifications: Configuration, settings, admins
  ⏸️  weather: Skipped (disabled)

📦 Creating backup archive...
  • Bot configurations: 4 entries
  • Custom settings: 19 parameters
  • Admin mappings: 8 unique admins
  • Webhook configurations: 4 endpoints
  • Class mappings: 4 bot classes

✅ Export completed: storage/app/tegbot/multibot_backup_2024-12-15_163045.json
📊 Archive size: 23.4KB
⏱️  Export time: 0.7s

💡 Restore with: php artisan teg:migrate import multibot_backup_2024-12-15_163045.json
```

### Селективный экспорт

```bash
# Экспорт только production ботов
php artisan teg:migrate export --enabled-only

# Экспорт конкретных ботов
php artisan teg:migrate export --bots=shop,support

# Экспорт с дополнительными данными
php artisan teg:migrate export --include-logs --include-stats
```

### Импорт с проверками

```bash
php artisan teg:migrate import backup.json --validate --dry-run
```

```
🔍 TegBot v2.0 Import Validation

📁 Reading backup: backup.json
✅ File format: Valid TegBot v2.0 export
✅ Bot count: 4 bots found
✅ Token format: All tokens valid format
✅ Name conflicts: No naming conflicts detected

📋 Import preview (DRY RUN):
  🆕 shop: Will be created (new bot)
  🔄 support: Will be updated (exists, changes detected)
  🆕 analytics: Will be created (new bot)  
  ⚠️  notifications: Will be skipped (disabled in backup)

🎯 Summary:
  • Will create: 2 bots
  • Will update: 1 bot  
  • Will skip: 1 bot
  • Estimated time: 3.2s

✅ Validation passed! Run without --dry-run to execute.
```

## Troubleshooting

### Распространенные проблемы

#### 1. Бот не отвечает на сообщения

```bash
# Диагностика конкретного бота
php artisan teg:bot test shop

# Проверка webhook
php artisan teg:webhook info shop

# Проверка логов
tail -f storage/logs/laravel.log | grep -i "shop\|webhook"
```

#### 2. Класс бота не найден

```bash
# Проверка существования класса
ls -la app/Bots/ShopBot.php

# Пересоздание класса
php artisan teg:bot regenerate-class shop
```

#### 3. Проблемы с базой данных

```bash
# Проверка таблицы ботов
php artisan tinker
>>> DB::table('tegbot_bots')->count()
>>> DB::table('tegbot_bots')->where('enabled', true)->get()

# Восстановление таблицы
php artisan migrate:refresh --path=database/migrations/2025_01_01_000000_create_tegbot_bots_table.php
```

#### 4. Конфликты webhook

```bash
# Проверка всех webhook
php artisan teg:webhook info --all

# Массовое обновление  
for bot in $(php artisan teg:bot list --format=json | jq -r '.[].name'); do
    php artisan teg:webhook set $bot
done
```

### Отладка мультиботной системы

```bash
# Включение подробного логирования
php artisan teg:config set logging.multibot_logs true
php artisan teg:config set logging.level debug

# Мониторинг в реальном времени
tail -f storage/logs/laravel.log | grep -E "(TegBot|webhook|multibot)"

# Проверка производительности
php artisan teg:stats --format=json | jq '.performance'
```

## Лучшие практики

### Именование и организация

```bash
# Хорошие имена ботов
shop              # E-commerce основной
shop_b2b          # B2B версия  
support_tier1     # Первая линия поддержки
support_tier2     # Вторая линия поддержки
analytics_sales   # Аналитика продаж
analytics_users   # Аналитика пользователей

# Плохие имена
bot1, mybot, test, bot_2024_final_v2
```

### Структура проекта

```
app/Bots/
├── Core/
│   ├── BaseShopBot.php      # Базовый класс для магазинов
│   ├── BaseSupportBot.php   # Базовый класс для поддержки
│   └── BaseAnalyticsBot.php # Базовый класс для аналитики
├── Shop/
│   ├── ShopBot.php          # Основной магазин
│   └── ShopB2bBot.php       # B2B магазин
├── Support/
│   ├── SupportTier1Bot.php  # Первая линия
│   └── SupportTier2Bot.php  # Вторая линия
└── Analytics/
    ├── SalesAnalyticsBot.php  # Аналитика продаж
    └── UsersAnalyticsBot.php  # Аналитика пользователей
```

### Мониторинг продакшена

```bash
# Cron задачи для мультиботной системы
# Каждые 2 минуты - быстрая проверка
*/2 * * * * php artisan teg:health --quiet --alert-on-error

# Каждые 10 минут - проверка webhook
*/10 * * * * php artisan teg:webhook test --all --quiet

# Ежечасно - статистика и очистка  
0 * * * * php artisan teg:stats --store-metrics && php artisan teg:cache clean

# Ежедневно в 3:00 - полный backup
0 3 * * * php artisan teg:migrate backup --auto --cleanup-days=30
```

## Заключение

TegBot v2.0 представляет собой революционный подход к созданию Telegram ботов:

🎯 **Основные преимущества:**
- **Масштабируемость**: От одного до сотен ботов в одном проекте
- **Централизация**: Единое управление всей экосистемой ботов  
- **Безопасность**: Индивидуальная изоляция каждого бота
- **Простота**: Автоматизация всех рутинных операций
- **Мониторинг**: Полная видимость состояния системы

🚀 **Начните прямо сейчас:**

```bash
php artisan teg:set  # Создать первого бота
php artisan teg:health  # Проверить систему  
php artisan teg:stats   # Посмотреть статистику
```

TegBot v2.0 - это не просто обновление, это **новая эра** в разработке Telegram ботов! 