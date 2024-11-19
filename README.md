
# Установка пакета TegBot

Для установки пакета используйте команду:

```bash
composer require tegbot/tegbot
```

## Публикация ассетов

Для публикации ассетов используйте команду:

```bash
php artisan vendor:publish --provider="Teg\Providers\TegbotServiceProvider"
```

## Создание бота

Для создания нового бота используйте команду:

```bash
php artisan teg:new
```
## Раздел бота

Все боты находяться в пути `app/Bots`, пример `MainBot`:

```php
<?php

namespace App\Bots;

class MainBot extends AdstractBot
{
    public function main(): void
    {
        $this->command("start", function () {
            $this->start();
        });
    }

    private function start()
    {
        $this->sendSelf('Hello Word');
    }
}
```

## Проверка бота

Бот готов к использованию. Для проверки введите команду:

```bash
/start
```
