<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bot;
use Illuminate\Support\Facades\Http;

class BotCommand extends Command
{
    protected $signature = 'bot:manage 
                            {action : Action (list, show, enable, disable, delete, test)}
                            {bot? : Bot name or ID}
                            {--format=table : Output format (table, json)}';
    
    protected $description = 'Управление ботами';

    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'list':
                return $this->listBots();
            case 'show':
                return $this->showBot();
            case 'enable':
                return $this->enableBot();
            case 'disable':
                return $this->disableBot();
            case 'delete':
                return $this->deleteBot();
            case 'test':
                return $this->testBot();
            default:
                $this->error("Неизвестное действие: {$action}");
                $this->line('Доступные действия: list, show, enable, disable, delete, test');
                return 1;
        }
    }

    private function listBots(): int
    {
        try {
            $bots = Bot::orderBy('created_at', 'desc')->get();

            if ($bots->isEmpty()) {
                $this->info('📭 Боты не найдены');
                $this->line('Используйте команду: php artisan bot:new');
                return 0;
            }

            $format = $this->option('format');

            if ($format === 'json') {
                $this->line(json_encode($bots->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                return 0;
            }

            $currentEnvironment = Bot::getCurrentEnvironment();
            $this->info('🤖 Список ботов:');
            $this->line("🌍 Текущее окружение: {$currentEnvironment}");
            $this->newLine();
            
            $this->table(
                ['ID', 'Имя', 'Username', 'Dev Token', 'Prod Token', 'Dev Domain', 'Prod Domain', 'Текущий Токен', 'Статус', 'Webhook', 'Создан'],
                $bots->map(function ($bot) use ($currentEnvironment) {
                    return [
                        $bot->id,
                        $bot->name,
                        '@' . $bot->username,
                        $bot->hasTokenForEnvironment('dev') ? '✅' : '❌',
                        $bot->hasTokenForEnvironment('prod') ? '✅' : '❌',
                        $bot->hasDomainForEnvironment('dev') ? '✅' : '❌',
                        $bot->hasDomainForEnvironment('prod') ? '✅' : '❌',
                        $bot->hasTokenForEnvironment($currentEnvironment) ? '✅' : '❌',
                        $bot->enabled ? '✅ Активен' : '❌ Отключен',
                        $bot->webhook_url ? '✅ Настроен' : '❌ Не настроен',
                        $bot->created_at->format('d.m.Y H:i')
                    ];
                })->toArray()
            );

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Ошибка: ' . $e->getMessage());
            $this->warn('💡 Убедитесь что запущены миграции: php artisan migrate');
            return 1;
        }
    }

    private function showBot(): int
    {
        $botIdentifier = $this->argument('bot');
        
        if (!$botIdentifier) {
            $botIdentifier = $this->ask('Введите имя или ID бота');
        }

        if (!$botIdentifier) {
            $this->error('❌ Имя или ID бота обязательны');
            return 1;
        }

        try {
            $bot = $this->findBot($botIdentifier);
            
            if (!$bot) {
                $this->error("❌ Бот '{$botIdentifier}' не найден");
                return 1;
            }

            $currentEnvironment = Bot::getCurrentEnvironment();
            $this->info("🤖 Информация о боте '{$bot->name}':");
            $this->newLine();

            $this->line("  📝 Имя: {$bot->name}");
            $this->line("  🆔 Username: @{$bot->username}");
            $this->line("  🔢 ID: {$bot->bot_id}");
            $this->line("  🌍 Текущее окружение: {$currentEnvironment}");
            $this->line("  🗝️  Dev Token: " . ($bot->hasTokenForEnvironment('dev') ? $bot->getMaskedTokenForEnvironment('dev') : '❌ Не установлен'));
            $this->line("  🗝️  Prod Token: " . ($bot->hasTokenForEnvironment('prod') ? $bot->getMaskedTokenForEnvironment('prod') : '❌ Не установлен'));
            $this->line("  🗝️  Текущий токен: " . ($bot->hasTokenForEnvironment($currentEnvironment) ? $bot->getMaskedTokenForEnvironment($currentEnvironment) : '❌ Не установлен'));
            $this->line("  🌐 Dev Domain: " . ($bot->hasDomainForEnvironment('dev') ? $bot->getDomainForEnvironment('dev') : '❌ Не установлен'));
            $this->line("  🌐 Prod Domain: " . ($bot->hasDomainForEnvironment('prod') ? $bot->getDomainForEnvironment('prod') : '❌ Не установлен'));
            $this->line("  🌐 Текущий домен: " . ($bot->hasDomainForEnvironment($currentEnvironment) ? $bot->getDomainForEnvironment($currentEnvironment) : '❌ Не установлен'));
            $this->line("  📡 Статус: " . ($bot->enabled ? '✅ Активен' : '❌ Отключен'));
            
            if ($bot->description) {
                $this->line("  📄 Описание: {$bot->description}");
            }
            
            if ($bot->webhook_url) {
                $this->line("  🌐 Webhook: {$bot->webhook_url}");
            }
            
            if ($bot->admin_ids && !empty($bot->admin_ids)) {
                $this->line("  👥 Администраторы: " . implode(', ', $bot->admin_ids));
            }
            
            $this->line("  📅 Создан: {$bot->created_at->format('d.m.Y H:i:s')}");
            $this->line("  🔄 Обновлен: {$bot->updated_at->format('d.m.Y H:i:s')}");
            
            // Проверяем класс бота
            if ($bot->botClassExists()) {
                $this->line("  🏗️  Класс: ✅ {$bot->getBotClass()}");
            } else {
                $this->line("  🏗️  Класс: ❌ {$bot->getBotClass()} (не найден)");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Ошибка: ' . $e->getMessage());
            return 1;
        }
    }

    private function enableBot(): int
    {
        $botIdentifier = $this->argument('bot');
        
        if (!$botIdentifier) {
            $botIdentifier = $this->ask('Введите имя или ID бота для активации');
        }

        try {
            $bot = $this->findBot($botIdentifier);
            
            if (!$bot) {
                $this->error("❌ Бот '{$botIdentifier}' не найден");
                return 1;
            }

            if ($bot->enabled) {
                $this->warn("⚠️  Бот '{$bot->name}' уже активен");
                return 0;
            }

            $bot->update(['enabled' => true]);
            $this->info("✅ Бот '{$bot->name}' активирован");

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Ошибка: ' . $e->getMessage());
            return 1;
        }
    }

    private function disableBot(): int
    {
        $botIdentifier = $this->argument('bot');
        
        if (!$botIdentifier) {
            $botIdentifier = $this->ask('Введите имя или ID бота для отключения');
        }

        try {
            $bot = $this->findBot($botIdentifier);
            
            if (!$bot) {
                $this->error("❌ Бот '{$botIdentifier}' не найден");
                return 1;
            }

            if (!$bot->enabled) {
                $this->warn("⚠️  Бот '{$bot->name}' уже отключен");
                return 0;
            }

            if (!$this->confirm("Отключить бота '{$bot->name}'?", false)) {
                $this->info('Отменено');
                return 0;
            }

            $bot->update(['enabled' => false]);
            $this->info("✅ Бот '{$bot->name}' отключен");

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Ошибка: ' . $e->getMessage());
            return 1;
        }
    }

    private function deleteBot(): int
    {
        $botIdentifier = $this->argument('bot');
        
        if (!$botIdentifier) {
            $botIdentifier = $this->ask('Введите имя или ID бота для удаления');
        }

        try {
            $bot = $this->findBot($botIdentifier);
            
            if (!$bot) {
                $this->error("❌ Бот '{$botIdentifier}' не найден");
                return 1;
            }

            $this->warn("⚠️  ВНИМАНИЕ: Это действие нельзя отменить!");
            $this->line("Будет удален бот: {$bot->name} (@{$bot->username})");
            
            if (!$this->confirm('Вы уверены?', false)) {
                $this->info('Отменено');
                return 0;
            }

            // Дополнительное подтверждение
            $confirmation = $this->ask("Введите имя бота '{$bot->name}' для подтверждения");
            if ($confirmation !== $bot->name) {
                $this->error('❌ Неверное подтверждение');
                return 1;
            }

            $bot->delete();
            $this->info("✅ Бот '{$bot->name}' удален");

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Ошибка: ' . $e->getMessage());
            return 1;
        }
    }

    private function testBot(): int
    {
        $botIdentifier = $this->argument('bot');
        
        if (!$botIdentifier) {
            $botIdentifier = $this->ask('Введите имя или ID бота для тестирования');
        }

        try {
            $bot = $this->findBot($botIdentifier);
            
            if (!$bot) {
                $this->error("❌ Бот '{$botIdentifier}' не найден");
                return 1;
            }

            $currentEnvironment = Bot::getCurrentEnvironment();
            $this->info("🧪 Тестирование бота '{$bot->name}'...");
            $this->line("🌍 Текущее окружение: {$currentEnvironment}");
            $this->newLine();

            // Проверяем наличие токена для текущего окружения
            if (!$bot->hasTokenForEnvironment($currentEnvironment)) {
                $this->error("❌ Токен для окружения '{$currentEnvironment}' не установлен");
                return 1;
            }

            // Тест API подключения
            $this->line('1. Проверка API подключения...');
            try {
                $token = $bot->getTokenForEnvironment($currentEnvironment);
                $response = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getMe");
                
                if ($response->successful()) {
                    $botInfo = $response->json()['result'];
                    $this->info('   ✅ API подключение работает');
                    $this->line("   📝 Имя: {$botInfo['first_name']}");
                    $this->line("   🆔 Username: @{$botInfo['username']}");
                } else {
                    $this->error('   ❌ Ошибка API: ' . $response->status());
                    return 1;
                }
            } catch (\Exception $e) {
                $this->error('   ❌ Ошибка соединения: ' . $e->getMessage());
                return 1;
            }

            // Тест класса бота
            $this->line('2. Проверка класса бота...');
            if ($bot->botClassExists()) {
                $this->info('   ✅ Класс бота найден: ' . $bot->getBotClass());
            } else {
                $this->error('   ❌ Класс бота не найден: ' . $bot->getBotClass());
            }

            // Тест webhook
            $this->line('3. Проверка webhook...');
            if ($bot->webhook_url) {
                try {
                    $token = $bot->getTokenForEnvironment($currentEnvironment);
                    $response = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getWebhookInfo");
                    
                    if ($response->successful()) {
                        $webhookInfo = $response->json()['result'];
                        
                        if ($webhookInfo['url'] === $bot->webhook_url) {
                            $this->info('   ✅ Webhook настроен корректно');
                        } else {
                            $this->warn("   ⚠️  Webhook URL не совпадает: {$webhookInfo['url']}");
                        }
                    } else {
                        $this->error('   ❌ Ошибка получения информации о webhook');
                    }
                } catch (\Exception $e) {
                    $this->error('   ❌ Ошибка проверки webhook: ' . $e->getMessage());
                }
            } else {
                $this->warn('   ⚠️  Webhook не настроен');
            }

            $this->newLine();
            $this->info('✅ Тестирование завершено');

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