<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bot;

class DomainCommand extends Command
{
    protected $signature = 'bot:domain 
                            {action : Action (set, show, list)}
                            {bot? : Bot name or ID}
                            {environment? : Environment (dev, prod)}
                            {domain? : Domain URL}';
    
    protected $description = 'Управление доменами ботов';

    public function handle()
    {
        $action = $this->argument('action');
        $botIdentifier = $this->argument('bot');

        switch ($action) {
            case 'set':
                return $this->setDomain($botIdentifier);
            case 'show':
                return $this->showDomains($botIdentifier);
            case 'list':
                return $this->listDomains();
            default:
                $this->error("Неизвестное действие: {$action}");
                $this->line('Доступные действия: set, show, list');
                return 1;
        }
    }

    private function setDomain(?string $botIdentifier): int
    {
        // Если бот не указан, запрашиваем выбор
        if (!$botIdentifier) {
            $bots = Bot::all();
            if ($bots->isEmpty()) {
                $this->error('❌ Нет ботов');
                $this->line('Создайте бота: php artisan bot:new');
                return 1;
            }

            $botNames = $bots->pluck('name')->toArray();
            $botIdentifier = $this->choice('Выберите бота:', $botNames);
        }

        // Находим бота
        $bot = $this->findBot($botIdentifier);
        if (!$bot) {
            $this->error("❌ Бот '{$botIdentifier}' не найден");
            return 1;
        }

        $environment = $this->argument('environment');
        if (!$environment) {
            $environment = $this->choice('Выберите окружение:', ['dev', 'prod']);
        }

        if (!in_array($environment, ['dev', 'prod'])) {
            $this->error('❌ Окружение должно быть dev или prod');
            return 1;
        }

        $domain = $this->argument('domain');
        if (!$domain) {
            $currentDomain = $bot->getDomainForEnvironment($environment);
            $domain = $this->ask("Введите домен для окружения {$environment}", $currentDomain);
        }

        if (!$domain) {
            $this->error('❌ Домен обязателен');
            return 1;
        }

        // Валидация домена
        if (!filter_var($domain, FILTER_VALIDATE_URL)) {
            $this->error('❌ Неверный формат домена');
            $this->line('Домен должен быть валидным URL (например: https://example.com)');
            return 1;
        }

        // Устанавливаем домен
        $bot->setDomainForEnvironment($environment, $domain);
        $bot->save();

        $this->info("✅ Домен для окружения '{$environment}' установлен: {$domain}");

        return 0;
    }

    private function showDomains(?string $botIdentifier): int
    {
        // Если бот не указан, запрашиваем выбор
        if (!$botIdentifier) {
            $bots = Bot::all();
            if ($bots->isEmpty()) {
                $this->error('❌ Нет ботов');
                return 1;
            }

            $botNames = $bots->pluck('name')->toArray();
            $botIdentifier = $this->choice('Выберите бота:', $botNames);
        }

        // Находим бота
        $bot = $this->findBot($botIdentifier);
        if (!$bot) {
            $this->error("❌ Бот '{$botIdentifier}' не найден");
            return 1;
        }

        $currentEnvironment = Bot::getCurrentEnvironment();
        $this->info("🌐 Домены для бота '{$bot->name}':");
        $this->newLine();

        $this->line("  🔧 Dev Domain: " . ($bot->hasDomainForEnvironment('dev') ? $bot->getDomainForEnvironment('dev') : '❌ Не установлен'));
        $this->line("  🚀 Prod Domain: " . ($bot->hasDomainForEnvironment('prod') ? $bot->getDomainForEnvironment('prod') : '❌ Не установлен'));
        $this->line("  🌍 Текущее окружение: {$currentEnvironment}");
        $this->line("  🌐 Текущий домен: " . ($bot->hasDomainForEnvironment($currentEnvironment) ? $bot->getDomainForEnvironment($currentEnvironment) : '❌ Не установлен'));

        return 0;
    }

    private function listDomains(): int
    {
        try {
            $bots = Bot::orderBy('name')->get();

            if ($bots->isEmpty()) {
                $this->info('📭 Боты не найдены');
                $this->line('Используйте команду: php artisan bot:new');
                return 0;
            }

            $currentEnvironment = Bot::getCurrentEnvironment();
            $this->info('🌐 Список доменов ботов:');
            $this->line("🌍 Текущее окружение: {$currentEnvironment}");
            $this->newLine();
            
            $this->table(
                ['ID', 'Имя', 'Username', 'Dev Domain', 'Prod Domain', 'Текущий Домен', 'Статус'],
                $bots->map(function ($bot) use ($currentEnvironment) {
                    return [
                        $bot->id,
                        $bot->name,
                        '@' . $bot->username,
                        $bot->hasDomainForEnvironment('dev') ? $bot->getDomainForEnvironment('dev') : '❌',
                        $bot->hasDomainForEnvironment('prod') ? $bot->getDomainForEnvironment('prod') : '❌',
                        $bot->hasDomainForEnvironment($currentEnvironment) ? $bot->getDomainForEnvironment($currentEnvironment) : '❌',
                        $bot->enabled ? '✅ Активен' : '❌ Отключен'
                    ];
                })->toArray()
            );

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Ошибка: ' . $e->getMessage());
            return 1;
        }
    }

    private function findBot(string $identifier): ?Bot
    {
        // Проверяем, это ID или имя
        if (is_numeric($identifier)) {
            return Bot::find($identifier);
        } else {
            return Bot::byName($identifier)->first();
        }
    }
} 