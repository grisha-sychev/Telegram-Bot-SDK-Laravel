# TegBot (Telegram Bot) SDK Laravel

![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-4479A1.svg?style=for-the-badge&logo=mysql&logoColor=white) 
![Redis](https://img.shields.io/badge/redis-%23DD0031.svg?style=for-the-badge&logo=redis&logoColor=white)

![Packagist Version](https://img.shields.io/packagist/v/tegbot/tegbot)

Представляет собой готовый набор инструментов для Laravel, который значительно упрощает процесс создания ботов для Telegram.

- [Все базовые методы](/)
- [Методы группы LightBot и в чем их примущество?](/)
- [Callback методы и их вся мощь](/)
- [Что такое AdstractBot?](/)
- [Состояние сообщений и как с ним работать?](/)
- [Все команды artisan](/)
  
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

use Teg\Modules\UserModule;
use Teg\Modules\StateModule;

class MainBot extends AdstractBot
{
    use StateModule, UserModule; // optional

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
