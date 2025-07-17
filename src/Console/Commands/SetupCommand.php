<?php

namespace Teg\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SetupCommand extends Command
{
    protected $signature = 'teg:set {--webhook= : Webhook URL} {--force : Force setup without confirmation}';
    protected $description = 'Настройка TegBot';

    public function handle()
    {
        $this->info('🚀 TegBot Setup Wizard');
        $this->newLine();

        // Проверяем токен
        $token = $this->checkToken();
        if (!$token) {
            return 1;
        }

        // Получаем информацию о боте
        $botInfo = $this->getBotInfo($token);
        if (!$botInfo) {
            return 1;
        }

        $this->displayBotInfo($botInfo);

        // Настраиваем webhook
        if ($this->option('webhook') || $this->confirm('Настроить webhook?', true)) {
            $this->setupWebhook($token);
        }

        // Проверяем и создаем конфигурацию
        $this->setupConfiguration();

        // Создаем директории
        $this->createDirectories();

        $this->newLine();
        $this->info('✅ Настройка TegBot завершена!');
        $this->line('📖 Документация: vendor/tegbot/tegbot/docs/');
        $this->line('🔍 Проверка: php artisan teg:health');

        return 0;
    }

    private function checkToken(): ?string
    {
        $token = config('tegbot.token', env('TEGBOT_TOKEN'));

        if (!$token) {
            $this->error('❌ TEGBOT_TOKEN не настроен');
            $this->line('Добавьте в .env файл:');
            $this->line('TEGBOT_TOKEN=your_bot_token_here');
            $this->newLine();
            $this->line('Получить токен: https://t.me/botfather');
            return null;
        }

        if (!preg_match('/^\d+:[A-Za-z0-9_-]{35}$/', $token)) {
            $this->error('❌ Неверный формат токена');
            return null;
        }

        $this->info('✅ Токен бота найден');
        return $token;
    }

    private function getBotInfo(string $token): ?array
    {
        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getMe");
            
            if ($response->successful()) {
                return $response->json()['result'];
            } else {
                $this->error('❌ Ошибка API: ' . $response->status());
                return null;
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка соединения: ' . $e->getMessage());
            return null;
        }
    }

    private function displayBotInfo(array $botInfo): void
    {
        $this->info('🤖 Информация о боте:');
        $this->line("  📝 Имя: {$botInfo['first_name']}");
        $this->line("  🆔 Username: @{$botInfo['username']}");
        $this->line("  📡 ID: {$botInfo['id']}");
        
        if (isset($botInfo['description'])) {
            $this->line("  📄 Описание: {$botInfo['description']}");
        }
        
        $this->newLine();
    }

    private function setupWebhook(string $token): void
    {
        $webhookUrl = $this->option('webhook');
        
        if (!$webhookUrl) {
            $webhookUrl = $this->ask('Введите URL webhook (например: https://yourdomain.com/telegram/webhook)');
        }

        if (!$webhookUrl) {
            $this->warn('⏭️  Пропускаем настройку webhook');
            return;
        }

        // Проверяем URL
        if (!filter_var($webhookUrl, FILTER_VALIDATE_URL) || !str_starts_with($webhookUrl, 'https://')) {
            $this->error('❌ URL должен быть HTTPS');
            return;
        }

        // Генерируем secret если не установлен
        $secret = config('tegbot.security.webhook_secret', env('TEGBOT_WEBHOOK_SECRET'));
        if (!$secret) {
            $secret = Str::random(32);
            $this->warn('⚠️  Webhook secret не настроен. Добавьте в .env:');
            $this->line("TEGBOT_WEBHOOK_SECRET={$secret}");
            $this->newLine();
        }

        // Устанавливаем webhook
        try {
            $payload = [
                'url' => $webhookUrl,
                'max_connections' => 40,
                'allowed_updates' => [
                    'message',
                    'callback_query',
                    'inline_query',
                    'chosen_inline_result'
                ]
            ];

            if ($secret) {
                $payload['secret_token'] = $secret;
            }

            $response = Http::post("https://api.telegram.org/bot{$token}/setWebhook", $payload);
            
            if ($response->successful()) {
                $this->info('✅ Webhook настроен успешно');
                $this->line("  🌐 URL: {$webhookUrl}");
            } else {
                $result = $response->json();
                $this->error('❌ Ошибка установки webhook: ' . ($result['description'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка установки webhook: ' . $e->getMessage());
        }
    }

    private function setupConfiguration(): void
    {
        $this->info('⚙️  Проверка конфигурации...');

        $configPath = config_path('tegbot.php');
        
        if (!file_exists($configPath)) {
            $this->warn('⚠️  Конфигурационный файл не найден');
            
            if ($this->confirm('Опубликовать конфигурацию?', true)) {
                $this->call('vendor:publish', [
                    '--provider' => 'Teg\Providers\TegbotServiceProvider',
                    '--tag' => 'config'
                ]);
            }
        } else {
            $this->info('✅ Конфигурационный файл найден');
        }

        // Проверяем основные настройки
        $this->checkConfigurationValues();
    }

    private function checkConfigurationValues(): void
    {
        $warnings = [];

        // Проверяем admin IDs
        $adminIds = config('tegbot.security.admin_ids', []);
        if (empty($adminIds)) {
            $warnings[] = 'TEGBOT_ADMIN_IDS не настроены';
        }

        // Проверяем webhook secret
        $webhookSecret = config('tegbot.security.webhook_secret');
        if (empty($webhookSecret)) {
            $warnings[] = 'TEGBOT_WEBHOOK_SECRET не установлен (риск безопасности)';
        }

        if (!empty($warnings)) {
            $this->warn('⚠️  Рекомендуемые настройки в .env:');
            foreach ($warnings as $warning) {
                $this->line("  - {$warning}");
            }
            $this->newLine();
        }
    }

    private function createDirectories(): void
    {
        $this->info('📁 Создание директорий...');

        $directories = [
            storage_path('app/tegbot/downloads'),
            storage_path('app/tegbot/temp'),
            storage_path('logs/tegbot'),
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                try {
                    mkdir($dir, 0755, true);
                    $this->line("  ✅ Создана: {$dir}");
                } catch (\Exception $e) {
                    $this->error("  ❌ Ошибка создания {$dir}: {$e->getMessage()}");
                }
            } else {
                $this->line("  ✅ Существует: {$dir}");
            }
        }
    }
} 