# Руководство по публикации файлов пакета

## Новая команда публикации

Вместо стандартной команды `vendor:publish` теперь доступна улучшенная команда `bot:publish` с возможностью принудительного обновления файлов.

## Использование

### Базовое использование

```bash
# Публикация всех файлов (пропускает существующие)
php artisan bot:publish

# Публикация с принудительным обновлением
php artisan bot:publish --force

# Публикация конкретных тегов
php artisan bot:publish --tag=bot-config --force
php artisan bot:publish --tag=bot-app --force
php artisan bot:publish --tag=bot-routes --force
```

### Доступные теги

- `bot` - все файлы (по умолчанию)
- `bot-config` или `config` - только конфигурация
- `bot-app` или `app` - только файлы приложения
- `bot-routes` или `routes` - только маршруты
- `bot-database` или `database` или `migrations` - только миграции
- `bot-lang` или `lang` - только языковые файлы

### Примеры использования

```bash
# Обновить только конфигурацию
php artisan bot:publish --tag=bot-config --force

# Обновить только команды и боты
php artisan bot:publish --tag=bot-app --force

# Обновить несколько тегов сразу
php artisan bot:publish --tag=bot-config --tag=bot-app --force

# Обновить все файлы
php artisan bot:publish --force
```

## Отличия от стандартной команды

### Стандартная команда Laravel
```bash
php artisan vendor:publish --provider="Bot\Providers\BotServiceProvider" --force
```

**Проблемы:**
- Не всегда корректно обрабатывает флаг `--force`
- Может пропускать файлы без предупреждения
- Ограниченная обратная связь

### Новая команда `bot:publish`
```bash
php artisan bot:publish --force
```

**Преимущества:**
- ✅ Гарантированное обновление файлов с флагом `--force`
- ✅ Подробная обратная связь о каждом файле
- ✅ Поддержка отдельных тегов
- ✅ Проверка существования файлов
- ✅ Создание директорий при необходимости

## Логирование операций

Команда показывает подробную информацию о каждом файле:

```
📦 Публикация файлов пакета Telegram Bot SDK...
⚠️  Режим принудительного обновления включен
🏷️  Теги для публикации: bot
📤 Принудительная публикация тега: bot
✅ Скопирован: bot.php
✅ Скопирован: SetupCommand.php
✅ Скопирован: WebhookCommand.php
⏭️  Пропущен (существует): .gitignore
✅ Публикация завершена!
```

## Структура публикуемых файлов

```
app/
├── Console/Commands/
│   ├── SetupCommand.php
│   ├── WebhookCommand.php
│   ├── EnvironmentCommand.php
│   └── PublishCommand.php
├── Models/
│   └── Bot.php
└── Bots/
    └── TestBot.php

config/
└── bot.php

database/migrations/
├── 0001_01_01_000000_create_user_telegrams_table.php
└── 2025_01_01_000000_create_bots_table.php

routes/
└── bot.php

resources/lang/
├── en/
│   └── messages.php
└── ru/
    └── messages.php
```

## Рекомендации по использованию

### Для разработки
```bash
# При первом использовании пакета
php artisan bot:publish

# При обновлении пакета
php artisan bot:publish --force
```

### Для продакшена
```bash
# Обновить только конфигурацию
php artisan bot:publish --tag=bot-config --force

# Обновить только миграции
php artisan bot:publish --tag=bot-database --force
```

### Для обновления конкретных компонентов
```bash
# Обновить только команды
php artisan bot:publish --tag=bot-app --force

# Обновить только маршруты
php artisan bot:publish --tag=bot-routes --force
```

## Безопасность

- Команда создает резервные копии важных файлов перед обновлением
- Проверяет права доступа к директориям
- Показывает предупреждения при пропуске файлов
- Логирует все операции для отладки

## Устранение неполадок

### Если файлы не обновляются
```bash
# Принудительно обновить все файлы
php artisan bot:publish --force

# Проверить права доступа
chmod -R 755 app/
chmod -R 755 config/
```

### Если команда не найдена
```bash
# Очистить кэш команд
php artisan config:clear
php artisan cache:clear
```

### Если возникают ошибки копирования
```bash
# Проверить свободное место на диске
df -h

# Проверить права доступа
ls -la app/
ls -la config/
``` 