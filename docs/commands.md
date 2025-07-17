# 🎯 Система команд TegBot v2.0

## Обзор

TegBot v2.0 предоставляет две системы команд:

- 🖥️ **Команды управления** (artisan): Управление мультиботной системой
- 🤖 **Команды ботов**: Команды внутри самих ботов для пользователей
- ⚙️ **Автоматизация**: Интеграция с cron и CI/CD
- 📊 **Мониторинг**: Диагностика и статистика через команды
- 🔧 **Конфигурация**: Настройка через командную строку

## Команды управления TegBot

### Основные команды

```bash
# Управление ботами
php artisan teg:set                 # Добавить нового бота
php artisan teg:bot list            # Список всех ботов
php artisan teg:bot show mybot      # Информация о боте
php artisan teg:bot enable mybot    # Активировать бота
php artisan teg:bot disable mybot   # Отключить бота
php artisan teg:bot delete mybot    # Удалить бота
php artisan teg:bot test mybot      # Тестировать бота

# Диагностика и мониторинг
php artisan teg:health              # Проверка здоровья системы
php artisan teg:stats               # Статистика всех ботов
php artisan teg:config validate     # Валидация конфигурации

# Управление webhook
php artisan teg:webhook info mybot  # Информация о webhook
php artisan teg:webhook set mybot   # Установить webhook
php artisan teg:webhook delete mybot # Удалить webhook

# Конфигурация
php artisan teg:config show         # Показать конфигурацию
php artisan teg:config set key value # Установить параметр
php artisan teg:config export       # Экспорт настроек

# Миграция и резервное копирование
php artisan teg:migrate export      # Экспорт ботов
php artisan teg:migrate import file # Импорт ботов
php artisan teg:migrate backup      # Резервное копирование
```

## 🤖 Управление ботами

### Добавление нового бота

```bash
php artisan teg:set
```

**Интерактивный процесс:**

```
🚀 TegBot Multi-Bot Setup Wizard

📋 Существующие боты:
┌────┬──────┬─────────────┬────────┬──────────────────┐
│ ID │ Имя  │ Username    │ Статус │ Создан           │
├────┼──────┼─────────────┼────────┼──────────────────┤
│ 1  │ shop │ @shopbot    │ ✅ Активен │ 01.12.2024 15:30 │
└────┴──────┴─────────────┴────────┴──────────────────┘

➕ Добавление нового бота

Введите имя бота (латинские буквы, без пробелов): news
Введите токен бота (полученный от @BotFather): 123456789:AABBccDD...
Введите ID администраторов (через запятую): 123456789

🤖 Информация о боте:
  📝 Имя: News Bot
  🆔 Username: @newsbot
  📡 ID: 123456789

✅ Бот сохранен в базу данных
📝 Создание класса бота NewsBot...
✅ Класс бота создан: app/Bots/NewsBot.php
🌐 Настройка webhook...
✅ Webhook установлен: https://yourdomain.com/webhook/news
✅ Настройка TegBot завершена!
```

### Управление существующими ботами

```bash
# Список всех ботов
php artisan teg:bot list

# Вывод:
🤖 Список ботов:
┌────┬─────────┬─────────────┬─────────────┬────────────┬─────────────┬──────────────────┐
│ ID │ Имя     │ Username    │ Токен       │ Статус     │ Webhook     │ Создан           │
├────┼─────────┼─────────────┼─────────────┼────────────┼─────────────┼──────────────────┤
│ 1  │ shop    │ @shopbot    │ 123456...*  │ ✅ Активен  │ ✅ Настроен  │ 01.12.2024 15:30 │
│ 2  │ news    │ @newsbot    │ 987654...*  │ ✅ Активен  │ ✅ Настроен  │ 15.12.2024 10:15 │
│ 3  │ support │ @supportbot │ 111222...*  │ ❌ Отключен │ ❌ Не настроен │ 20.12.2024 14:45 │
└────┴─────────┴─────────────┴─────────────┴────────────┴─────────────┴──────────────────┘
```

```bash
# Детальная информация о боте
php artisan teg:bot show shop

# Вывод:
🤖 Информация о боте 'shop':

  📝 Имя: shop
  🆔 Username: @shopbot
  🔢 ID: 123456789
  🗝️  Токен: 123456789:******
  📡 Статус: ✅ Активен
  📄 Описание: E-commerce bot for online store
  🌐 Webhook: https://yourdomain.com/webhook/shop
  👥 Администраторы: 123456789, 987654321
  📅 Создан: 01.12.2024 15:30:45
  🔄 Обновлен: 15.12.2024 09:20:15
  🏗️  Класс: ✅ App\Bots\ShopBot
```

### Активация и деактивация ботов

```bash
# Активация бота
php artisan teg:bot enable support
✅ Бот 'support' активирован

# Отключение бота
php artisan teg:bot disable support
✅ Бот 'support' отключен

# Тестирование бота
php artisan teg:bot test shop

# Вывод:
🧪 Тестирование бота 'shop'

✅ База данных: Бот найден в БД
✅ Класс бота: App\Bots\ShopBot существует
✅ Telegram API: Токен валиден
✅ Webhook: https://yourdomain.com/webhook/shop отвечает
✅ Права доступа: Файлы доступны для записи

🎯 Результат: Бот полностью работоспособен
```

### Удаление ботов

```bash
php artisan teg:bot delete support

# Интерактивное подтверждение:
⚠️  ВНИМАНИЕ: Это действие нельзя отменить!
Будет удален бот: support (@supportbot)
Вы уверены? (yes/no) [no]: yes

Введите имя бота 'support' для подтверждения: support
✅ Бот 'support' удален
```

## 📊 Диагностика и мониторинг

### Проверка здоровья системы

```bash
php artisan teg:health
```

**Подробный вывод:**

```
🔍 Проверка состояния мультиботной системы TegBot

✅ Система:
  ✅ PHP версия: 8.2.0 (совместима)
  ✅ Laravel версия: 10.0 (совместима)
  ✅ Память: 45MB / 512MB (9%)
  ✅ Диск: 2.3GB / 10GB (23%)

✅ База данных:
  ✅ Подключение: Успешно (15ms)
  ✅ Таблица ботов: Найдена (3 бота)
  ✅ Миграции: Все применены

✅ Кэш и очереди:
  ✅ Redis: Подключение работает
  ✅ Кэш: 127 ключей, 45MB
  ✅ Очереди: 0 задач в ожидании

🤖 Проверка ботов:
  ✅ shop (@shopbot):
    ✅ Статус: Активен
    ✅ Класс: App\Bots\ShopBot найден
    ✅ Telegram API: Токен валиден (150ms)
    ✅ Webhook: https://yourdomain.com/webhook/shop работает
    ✅ Администраторы: 2 настроено

  ✅ news (@newsbot):
    ✅ Статус: Активен
    ✅ Класс: App\Bots\NewsBot найден
    ✅ Telegram API: Токен валиден (135ms)
    ✅ Webhook: https://yourdomain.com/webhook/news работает
    ✅ Администраторы: 1 настроено

  ❌ support (@supportbot):
    ❌ Статус: Отключен
    ⚠️  Webhook: Не настроен
    ℹ️  Класс: App\Bots\SupportBot найден

📋 Конфигурация:
  ✅ Webhook secret: Установлен
  ✅ Пути для файлов: Доступны для записи
  ✅ Лимиты безопасности: Настроены

⚠️  Предупреждения:
  - Бот 'support' отключен
  - TEGBOT_QUEUE=false (рекомендуется включить для продакшена)

📊 Итог:
  • Всего ботов: 3
  • Активных: 2
  • Отключенных: 1
  • Ошибок: 0
  • Предупреждений: 2

🎯 Общий статус: ✅ Система работает нормально
```

### Проверка конкретного бота

```bash
php artisan teg:health --bot=shop

# Фокусированная проверка одного бота
🔍 Проверка состояния бота 'shop'

✅ Основная информация:
  • Имя: shop
  • Username: @shopbot
  • ID в Telegram: 123456789
  • Статус: Активен

✅ Технические проверки:
  ✅ Класс бота: App\Bots\ShopBot
  ✅ Токен: Валиден, последняя проверка 5 минут назад
  ✅ Webhook: https://yourdomain.com/webhook/shop (отвечает за 120ms)
  ✅ Администраторы: 2 ID настроено

✅ API тестирование:
  ✅ getMe: OK (145ms)
  ✅ getWebhookInfo: OK, 0 ожидающих обновлений
  ✅ Права: Может отправлять сообщения

🎯 Результат: Бот полностью работоспособен
```

### Статистика системы

```bash
php artisan teg:stats
```

```
📊 TegBot Statistics

🤖 Боты (за последние 24 часа):
┌──────────┬─────────────┬───────────┬──────────┬───────────┐
│ Бот      │ Сообщений   │ Команд    │ Ошибок   │ Время     │
├──────────┼─────────────┼───────────┼──────────┼───────────┤
│ shop     │ 1,247       │ 156       │ 2        │ 145ms     │
│ news     │ 892         │ 89        │ 0        │ 132ms     │
│ support  │ 0 (отключен)│ 0         │ 0        │ -         │
└──────────┴─────────────┴───────────┴──────────┴───────────┘

📈 Общая статистика:
  • Всего сообщений: 2,139
  • Всего команд: 245
  • Успешность: 99.1%
  • Среднее время ответа: 138ms

🔥 Топ команды:
  1. /start - 89 вызовов
  2. /help - 67 вызовов
  3. /search - 45 вызовов
  4. /catalog - 31 вызов
  5. /info - 13 вызовов

📱 Пользователи:
  • Всего пользователей: 1,456
  • Новых за сегодня: 23
  • Активных за неделю: 892

🚨 Ошибки (последние):
  [15:30] shop: API timeout (resolved)
  [12:45] shop: Invalid user ID in /ban command
```

```bash
# Детальная статистика
php artisan teg:stats --detailed

# Статистика за разные периоды
php artisan teg:stats --period=7d   # За неделю
php artisan teg:stats --period=30d  # За месяц
php artisan teg:stats --period=1h   # За час

# Экспорт в JSON
php artisan teg:stats --format=json > stats.json
```

## 🌐 Управление Webhook

### Информация о webhook

```bash
php artisan teg:webhook info shop
```

```
🌐 Webhook информация для бота 'shop'

📡 Основные данные:
  • URL: https://yourdomain.com/webhook/shop
  • Статус: ✅ Активен
  • Последняя проверка: 2 минуты назад
  • Secret token: Установлен

📊 Статистика Telegram:
  • Ожидающих обновлений: 0
  • Последняя ошибка: Нет
  • Максимальные соединения: 40
  • Разрешенные обновления: message, callback_query

🔧 Технические детали:
  • IP-фильтр: 149.154.160.0/20, 91.108.4.0/22
  • SSL проверка: Включена
  • Таймаут: 30 секунд

🔄 Последняя активность:
  • Последнее обновление: 15:34:26
  • Всего за сегодня: 1,247 обновлений
  • Ошибок: 0
```

### Установка webhook

```bash
# Установка webhook для конкретного бота
php artisan teg:webhook set shop https://yourdomain.com/webhook/shop

# Автоматическая установка на основе конфигурации
php artisan teg:webhook set shop

# Установка с кастомными параметрами
php artisan teg:webhook set shop --secret=my_secret --max-connections=100
```

### Удаление webhook

```bash
php artisan teg:webhook delete shop

⚠️  Удаление webhook для бота 'shop'
Бот перестанет получать обновления от Telegram.
Продолжить? (yes/no) [no]: yes

✅ Webhook удален для бота 'shop'
```

## ⚙️ Управление конфигурацией

### Просмотр конфигурации

```bash
# Полная конфигурация
php artisan teg:config show

# Конкретный параметр
php artisan teg:config get multibot.enabled
> true

# Конфигурация в разных форматах
php artisan teg:config show --format=json
php artisan teg:config show --format=yaml
```

### Изменение настроек

```bash
# Установка глобального параметра
php artisan teg:config set multibot.max_bots 200
✅ Параметр multibot.max_bots установлен в 200

# Настройка конкретного бота
php artisan teg:bot config shop --setting=rate_limit --value=50
✅ Настройка rate_limit для бота 'shop' установлена в 50

# Настройка администраторов
php artisan teg:bot admin shop --add=555666777
✅ Администратор 555666777 добавлен к боту 'shop'

php artisan teg:bot admin shop --remove=999888777  
✅ Администратор 999888777 удален из бота 'shop'
```

### Валидация конфигурации

```bash
php artisan teg:config validate
```

```
⚙️  Валидация конфигурации TegBot

✅ Глобальная конфигурация:
  ✅ Webhook secret: Установлен (32 символа)
  ✅ Пути для файлов: Существуют и доступны для записи
  ✅ Redis: Подключение работает
  ✅ База данных: Доступна, все таблицы найдены
  ✅ Лимиты безопасности: Настроены корректно

🤖 Проверка ботов:
  ✅ shop (@shopbot):
    ✅ Токен: Валидный формат и работает с API
    ✅ Администраторы: 2 ID, все валидные
    ✅ Настройки: JSON корректен
    ✅ Класс: App\Bots\ShopBot найден и валиден

  ✅ news (@newsbot):
    ✅ Токен: Валидный формат и работает с API  
    ✅ Администраторы: 1 ID, валидный
    ✅ Настройки: JSON корректен
    ✅ Класс: App\Bots\NewsBot найден и валиден

  ❌ support (@supportbot):
    ❌ Администраторы: Не указаны (рекомендуется добавить)
    ⚠️  Статус: Отключен

⚠️  Рекомендации:
  - Добавьте администраторов для бота 'support'
  - TEGBOT_CACHE_DRIVER=file, рекомендуется Redis для продакшена
  - TEGBOT_QUEUE=false, включите очереди для высокой нагрузки

📊 Итог: 1 ошибка, 3 предупреждения из 15 проверок
```

## 💾 Миграция и резервное копирование

### Экспорт ботов

```bash
# Экспорт всех ботов
php artisan teg:migrate export

# Вывод:
💾 Экспорт данных TegBot

📋 Экспортируемые данные:
  • Боты: 3
  • Настройки: Все
  • Администраторы: Все
  • Webhook URLs: Все

📁 Создание резервной копии...
✅ Экспорт завершен: storage/app/tegbot/backup_2024-12-15_163045.json

📊 Статистика экспорта:
  • Размер файла: 15.2KB
  • Время выполнения: 0.3s
  • Статус: Успешно
```

```bash
# Экспорт в конкретный файл
php artisan teg:migrate export --path=my_backup.json

# Экспорт в формате CSV
php artisan teg:migrate export --format=csv

# Экспорт только конкретного бота
php artisan teg:migrate export --bot=shop
```

### Импорт ботов

```bash
php artisan teg:migrate import backup_2024-12-15.json

⚠️  Импорт данных TegBot
Это действие может перезаписать существующих ботов.
Продолжить? (yes/no) [no]: yes

📁 Читаем файл: backup_2024-12-15.json
✅ Файл валиден, найдено 3 бота

📋 Импортируемые боты:
  • shop (@shopbot) - будет обновлен
  • news (@newsbot) - будет создан
  • support (@supportbot) - будет создан

⚡ Импорт...
✅ shop: Обновлен
✅ news: Создан
✅ support: Создан

📊 Результат импорта:
  • Обновлено: 1
  • Создано: 2
  • Ошибок: 0
  • Время: 0.8s
```

### Автоматическое резервное копирование

```bash
# Создание cron задачи для ежедневного бэкапа
0 3 * * * cd /path/to/project && php artisan teg:migrate backup --auto

# Создание backup с автоматической очисткой старых
php artisan teg:migrate backup --cleanup-days=30
```

## 🔄 Автоматизация

### Cron задачи

```bash
# Файл crontab для TegBot
# Проверка здоровья каждые 5 минут
*/5 * * * * cd /path/to/project && php artisan teg:health --quiet

# Ежедневная статистика в 9:00
0 9 * * * cd /path/to/project && php artisan teg:stats --period=24h | mail -s "TegBot Daily Stats" admin@example.com

# Еженедельное резервное копирование в воскресенье в 2:00
0 2 * * 0 cd /path/to/project && php artisan teg:migrate backup --auto

# Ежемесячная очистка логов в первый день месяца в 3:00
0 3 1 * * cd /path/to/project && php artisan teg:logs clean --older-than=90d

# Валидация конфигурации каждые 15 минут
*/15 * * * * cd /path/to/project && php artisan teg:config validate --quiet --alert-on-error
```

### CI/CD интеграция

```yaml
# .github/workflows/tegbot.yml
name: TegBot Deployment

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          
      - name: Install dependencies
        run: composer install --no-dev --optimize-autoloader
        
      - name: Run migrations
        run: php artisan migrate --force
        
      - name: Validate TegBot configuration
        run: php artisan teg:config validate
        
      - name: Test all bots
        run: |
          for bot in $(php artisan teg:bot list --format=json | jq -r '.[].name'); do
            php artisan teg:bot test $bot
          done
          
      - name: Health check
        run: php artisan teg:health
        
      - name: Create deployment backup
        run: php artisan teg:migrate export --path=deployment-backup-$(date +%Y%m%d-%H%M%S).json
```

### Скрипты мониторинга

```bash
#!/bin/bash
# monitor-tegbot.sh - скрипт мониторинга TegBot

# Проверка здоровья
health_check() {
    echo "🔍 Checking TegBot health..."
    php artisan teg:health --no-interaction
    
    if [ $? -ne 0 ]; then
        echo "❌ Health check failed!"
        # Отправка уведомления
        curl -X POST "https://api.telegram.org/bot$ALERT_BOT_TOKEN/sendMessage" \
             -d "chat_id=$ALERT_CHAT_ID" \
             -d "text=🚨 TegBot health check failed!"
        exit 1
    fi
    
    echo "✅ Health check passed"
}

# Проверка активности ботов
activity_check() {
    echo "📊 Checking bot activity..."
    
    # Получаем статистику за последний час
    stats=$(php artisan teg:stats --period=1h --format=json)
    
    # Проверяем есть ли активность
    total_messages=$(echo $stats | jq '.performance.total_messages')
    
    if [ "$total_messages" -eq 0 ]; then
        echo "⚠️  No activity in the last hour"
        # Можно добавить алерт
    else
        echo "✅ Activity detected: $total_messages messages"
    fi
}

# Очистка старых файлов
cleanup() {
    echo "🧹 Cleaning up old files..."
    
    # Очистка старых бэкапов (старше 30 дней)
    find storage/app/tegbot/backups -name "*.json" -mtime +30 -delete
    
    # Очистка временных файлов
    php artisan teg:cache clean --older-than=24h
    
    echo "✅ Cleanup completed"
}

# Главная функция
main() {
    echo "🚀 TegBot Monitor Script Started $(date)"
    
    health_check
    activity_check
    cleanup
    
    echo "✅ Monitor script completed $(date)"
}

main "$@"
```

## 🤖 Команды внутри ботов

Помимо команд управления системой, TegBot поддерживает создание команд внутри ваших ботов для пользователей.

### Регистрация команд в боте

```php
// app/Bots/ShopBot.php
class ShopBot extends LightBot
{
    public function commands(): void
    {
        // Простая команда
        $this->registerCommand('start', function () {
            $this->sendSelf('🛍️ Добро пожаловать в наш магазин!');
        }, [
            'description' => 'Запуск бота'
        ]);

        // Команда с аргументами
        $this->registerCommand('search', function ($args) {
            $query = $args[0] ?? null;
            if (!$query) {
                $this->sendSelf('❌ Укажите поисковый запрос');
                return;
            }
            $this->handleSearch($query);
        }, [
            'description' => 'Поиск товаров',
            'args' => ['query']
        ]);

        // Административная команда
        $this->registerCommand('admin', function ($args) {
            $this->showAdminPanel();
        }, [
            'description' => 'Панель администратора',
            'admin_only' => true,
            'private_only' => true
        ]);
    }

    public function fallback(): void
    {
        $this->sendSelf('❓ Неизвестная команда. Используйте /help для справки.');
    }
}
```

### Генерация справки по командам

```php
// Автоматическая справка на основе зарегистрированных команд
private function showHelp(): void
{
    $helpText = $this->generateHelp();
    $this->sendSelf($helpText);
}

// Результат:
// 🛍️ **Справка по командам**
//
// /start - Запуск бота
// /search <query> - Поиск товаров  
// /help - Показать справку
// /admin - Панель администратора (только админы)
//
// 💡 *<> - обязательный параметр, [] - опциональный*
```

## 🔧 Расширенные возможности

### Batch операции

```bash
# Выполнение команды для всех ботов
for bot in $(php artisan teg:bot list --format=json | jq -r '.[].name'); do
    php artisan teg:bot test $bot
done

# Массовое обновление webhook
php artisan teg:bot list --format=json | jq -r '.[].name' | xargs -I {} php artisan teg:webhook set {}

# Массовая активация отключенных ботов
php artisan teg:bot list --format=json | jq -r '.[] | select(.enabled == false) | .name' | xargs -I {} php artisan teg:bot enable {}
```

### Алерты и уведомления

```bash
# Настройка алертов при ошибках
php artisan teg:config set monitoring.alerts.enabled true
php artisan teg:config set monitoring.alerts.email admin@example.com

# Отправка уведомлений в Telegram при проблемах
php artisan teg:health --alert-bot=alerts --alert-chat=-123456789
```

---

🎯 **Система команд TegBot v2.0** - полный контроль над мультиботной экосистемой! 