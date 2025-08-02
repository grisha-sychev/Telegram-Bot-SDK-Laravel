<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Bot;

class WebhookCommand extends Command
{
    protected $signature = 'bot:webhook 
                            {action : Action (set, info, delete, test, auto, restart)}
                            {bot? : Bot name or ID}
                            {url? : Webhook URL (for set action)}
                            {--secret= : Webhook secret token}
                            {--max-connections=40 : Max webhook connections}
                            {--no-ssl : Disable SSL verification}
                            {--force : Force action without confirmation}
                            {--environment= : Environment (dev, prod) - if not specified, uses current APP_ENV}';
    
    protected $description = 'Управление webhook Bot';

    public function handle()
    {
        $action = $this->argument('action');
        $botIdentifier = $this->argument('bot');

        // Если бот не указан, запрашиваем выбор
        if (!$botIdentifier) {
            $bots = Bot::enabled()->get();
            if ($bots->isEmpty()) {
                $this->error('❌ Нет активных ботов');
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

        // Определяем окружение (приоритет: опция --environment, затем APP_ENV)
        $environment = $this->option('environment');
        if ($environment && !in_array($environment, ['dev', 'prod'])) {
            $this->error('❌ Окружение должно быть dev или prod');
            return 1;
        }
        
        $currentEnvironment = $environment ?: Bot::getCurrentEnvironment();
        
        // Проверяем наличие токена для указанного окружения
        if (!$bot->hasTokenForEnvironment($currentEnvironment)) {
            $this->error("❌ Токен для окружения '{$currentEnvironment}' не установлен у бота '{$bot->name}'");
            return 1;
        }

        $token = $bot->getTokenForEnvironment($currentEnvironment);
        
        // Показываем информацию об используемом окружении
        if ($environment) {
            $this->info("🌍 Используется окружение: {$currentEnvironment}");
        }

        switch ($action) {
            case 'set':
                return $this->setWebhook($bot, $token);
            case 'info':
                return $this->getWebhookInfo($bot, $token);
            case 'delete':
                return $this->deleteWebhook($bot, $token);
            case 'test':
                return $this->testWebhook($bot, $token);
            case 'auto':
                return $this->autoWebhook($bot, $token, $currentEnvironment);
            case 'restart':
                return $this->restartWebhook($bot, $token, $currentEnvironment);
            case 'check':
                return $this->checkWebhook($bot, $token, $currentEnvironment);
            default:
                $this->error("Неизвестное действие: {$action}");
                $this->line('Доступные действия: set, info, delete, test, auto, restart, check');
                return 1;
        }
    }

    private function setWebhook(Bot $bot, string $token): int
    {
        $url = $this->argument('url');
        $currentEnvironment = Bot::getCurrentEnvironment();
        
        if (!$url) {
            // Используем домен из базы данных для текущего окружения
            $domain = $bot->getDomainForEnvironment($currentEnvironment);
            
            if ($domain) {
                $defaultUrl = rtrim($domain, '/') . "/webhook/{$bot->name}";
                $url = $this->ask('Введите URL webhook', $defaultUrl);
            } else {
                $url = $this->ask('Введите URL webhook');
            }
        }

        if (!$url) {
            $this->error('❌ URL обязателен');
            return 1;
        }

        // Валидация URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error('❌ Неверный формат URL');
            return 1;
        }

        // Проверяем HTTPS (кроме локальных адресов)
        $isLocal = str_contains($url, 'localhost') ||
            str_contains($url, '127.0.0.1') ||
            str_contains($url, '192.168.') ||
            str_contains($url, '.local');

        if (!str_starts_with($url, 'https://') && !$isLocal) {
            $this->error('❌ URL должен использовать HTTPS (кроме локальных адресов)');
            return 1;
        }

        if (!str_starts_with($url, 'https://') && $isLocal) {
            $this->warn('⚠️  Используется HTTP соединение (только для разработки!)');
        }

        // Подготавливаем параметры
        $secret = $this->option('secret') ?? config('bot.security.webhook_secret', env('BOT_WEBHOOK_SECRET'));
        $maxConnections = $this->option('max-connections');

        if (!$secret) {
            if ($this->confirm('Генерировать webhook secret автоматически?', true)) {
                $secret = Str::random(32);
                $this->warn("💡 Сгенерирован secret: {$secret}");
                $this->warn('Добавьте в .env файл:');
                $this->line("BOT_WEBHOOK_SECRET={$secret}");
                $this->newLine();
            }
        }

        $payload = [
            'url' => $url,
            'max_connections' => $maxConnections,
            'allowed_updates' => [
                'message',
                'callback_query',
                'inline_query',
                'chosen_inline_result',
                'channel_post',
                'edited_message',
                'edited_channel_post'
            ]
        ];

        if ($secret) {
            $payload['secret_token'] = $secret;
        }

        $this->info("🔧 Настройка webhook для бота '{$bot->name}'...");
        $this->line("🌐 URL: {$url}");
        $this->line("🌍 Окружение: {$currentEnvironment}");

        try {
            $response = Http::timeout(30)->post("https://api.telegram.org/bot{$token}/setWebhook", $payload);

            if ($response->successful()) {
                // Сохраняем webhook данные в БД (относительный путь)
                $relativeUrl = "/webhook/{$bot->name}";
                $bot->update([
                    'webhook_url' => $relativeUrl,
                    'webhook_secret' => $secret,
                ]);

                $this->info('✅ Webhook настроен успешно');
                $this->line("🌐 Полный URL: {$url}");
                $this->line("📝 Относительный URL в БД: {$relativeUrl}");
                if ($secret) {
                    $this->line("🔐 Secret: {$secret}");
                }
            } else {
                $result = $response->json();
                $this->error('❌ Ошибка установки webhook: ' . ($result['description'] ?? 'Unknown error'));
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка установки webhook: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function getWebhookInfo(Bot $bot, string $token): int
    {
        $this->info("🔍 Получение информации о webhook для бота '{$bot->name}'...");

        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getWebhookInfo");

            if ($response->successful()) {
                $info = $response->json()['result'];
                $this->displayWebhookInfo($info);
            } else {
                $this->error('❌ Ошибка получения информации о webhook');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function deleteWebhook(Bot $bot, string $token): int
    {
        $this->info("🗑️  Удаление webhook для бота '{$bot->name}'...");

        if (!$this->option('force') && !$this->confirm('Вы уверены, что хотите удалить webhook?', false)) {
            $this->info('Отменено');
            return 0;
        }

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/deleteWebhook");

            if ($response->successful()) {
                // Очищаем webhook данные в БД
                $bot->update([
                    'webhook_url' => null,
                    'webhook_secret' => null,
                ]);

                $this->info('✅ Webhook удален успешно');
            } else {
                $result = $response->json();
                $this->error('❌ Ошибка удаления webhook: ' . ($result['description'] ?? 'Unknown error'));
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка удаления webhook: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function testWebhook(Bot $bot, string $token): int
    {
        $this->info("🧪 Тестирование webhook для бота '{$bot->name}'...");

        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getWebhookInfo");

            if ($response->successful()) {
                $info = $response->json()['result'];
                
                if (!$info['url']) {
                    $this->warn('⚠️  Webhook не настроен');
                    return 0;
                }

                $this->line("🌐 URL: {$info['url']}");
                $this->line("📊 Ошибок: " . ($info['last_error_message'] ?? 'Нет'));
                $this->line("📅 Последнее обновление: " . ($info['last_error_date'] ? date('d.m.Y H:i:s', $info['last_error_date']) : 'Нет'));

                // Проверяем SSL сертификат
                $this->checkSSL($info['url']);

                // Отправляем тестовое обновление
                if ($this->confirm('Отправить тестовое обновление?', false)) {
                    $secret = $bot->webhook_secret;
                    $this->sendTestUpdate($info['url'], $secret);
                }
            } else {
                $this->error('❌ Ошибка получения информации о webhook');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function autoWebhook(Bot $bot, string $token, string $currentEnvironment): int
    {
        $this->info("🔄 Автоматическое обновление webhook для бота '{$bot->name}'...");
        $this->line("🌍 Текущее окружение: {$currentEnvironment}");

        // Получаем домен для текущего окружения
        $domain = $bot->getDomainForEnvironment($currentEnvironment);
        if (!$domain) {
            $this->error("❌ Домен для окружения '{$currentEnvironment}' не установлен");
            return 1;
        }

        // Формируем webhook URL
        $webhookUrl = rtrim($domain, '/') . "/webhook/{$bot->name}";
        $secret = $bot->webhook_secret;

        $this->info("🌐 Webhook URL: {$webhookUrl}");

        try {
            // Удаляем старый webhook
            $this->info('🗑️  Удаление старого webhook...');
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/deleteWebhook");
            
            if (!$response->successful()) {
                $result = $response->json();
                $this->warn('⚠️  Ошибка удаления webhook: ' . ($result['description'] ?? 'Unknown error'));
            } else {
                $this->info('✅ Старый webhook удален');
            }

            // Устанавливаем новый webhook
            $this->info('🔧 Установка нового webhook...');
            
            $payload = [
                'url' => $webhookUrl,
                'max_connections' => 40,
                'allowed_updates' => [
                    'message',
                    'callback_query',
                    'inline_query',
                    'chosen_inline_result',
                    'channel_post',
                    'edited_message',
                    'edited_channel_post'
                ]
            ];

            if ($secret) {
                $payload['secret_token'] = $secret;
            }

            $response = Http::timeout(30)->post("https://api.telegram.org/bot{$token}/setWebhook", $payload);

            if ($response->successful()) {
                // Сохраняем webhook данные в БД (относительный путь)
                $relativeUrl = "/webhook/{$bot->name}";
                $bot->update([
                    'webhook_url' => $relativeUrl,
                    'webhook_secret' => $secret,
                ]);

                $this->info('✅ Webhook обновлен успешно!');
                $this->line("🌐 Полный URL: {$webhookUrl}");
                $this->line("📝 Относительный URL в БД: {$relativeUrl}");
                $this->line("🌍 Окружение: {$currentEnvironment}");
                if ($secret) {
                    $this->line("🔐 Secret: {$secret}");
                }
                
            } else {
                $result = $response->json();
                $this->error('❌ Ошибка установки webhook: ' . ($result['description'] ?? 'Unknown error'));
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка обновления webhook: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function restartWebhook(Bot $bot, string $token, string $currentEnvironment): int
    {
        $this->info("🔄 Перезапуск webhook для бота '{$bot->name}'...");
        $this->line("🌍 Текущее окружение: {$currentEnvironment}");

        // Получаем домен для текущего окружения
        $domain = $bot->getDomainForEnvironment($currentEnvironment);
        if (!$domain) {
            $this->error("❌ Домен для окружения '{$currentEnvironment}' не установлен");
            return 1;
        }

        // Формируем webhook URL
        $webhookUrl = rtrim($domain, '/') . "/webhook/{$bot->name}";
        $secret = $bot->webhook_secret;

        $this->info("🌐 Webhook URL: {$webhookUrl}");

        try {
            // Удаляем старый webhook
            $this->info('🗑️  Удаление старого webhook...');
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/deleteWebhook");
            
            if (!$response->successful()) {
                $result = $response->json();
                $this->warn('⚠️  Ошибка удаления webhook: ' . ($result['description'] ?? 'Unknown error'));
            } else {
                $this->info('✅ Старый webhook удален');
            }

            // Устанавливаем новый webhook
            $this->info('🔧 Установка нового webhook...');
            
            $payload = [
                'url' => $webhookUrl,
                'max_connections' => 40,
                'allowed_updates' => [
                    'message',
                    'callback_query',
                    'inline_query',
                    'chosen_inline_result',
                    'channel_post',
                    'edited_message',
                    'edited_channel_post'
                ]
            ];

            if ($secret) {
                $payload['secret_token'] = $secret;
            }

            $response = Http::timeout(30)->post("https://api.telegram.org/bot{$token}/setWebhook", $payload);

            if ($response->successful()) {
                // Сохраняем webhook данные в БД (относительный путь)
                $relativeUrl = "/webhook/{$bot->name}";
                $bot->update([
                    'webhook_url' => $relativeUrl,
                    'webhook_secret' => $secret,
                ]);

                $this->info('✅ Webhook перезапущен успешно!');
                $this->line("🌐 Полный URL: {$webhookUrl}");
                $this->line("📝 Относительный URL в БД: {$relativeUrl}");
                $this->line("🌍 Окружение: {$currentEnvironment}");
                if ($secret) {
                    $this->line("🔐 Secret: {$secret}");
                }
                
            } else {
                $result = $response->json();
                $this->error('❌ Ошибка установки webhook: ' . ($result['description'] ?? 'Unknown error'));
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка перезапуска webhook: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function checkWebhook(Bot $bot, string $token, string $currentEnvironment): int
    {
        $this->info("🔍 Проверка соответствия webhook для бота '{$bot->name}'...");
        $this->line("🌍 Текущее окружение: {$currentEnvironment}");

        // Получаем домен для текущего окружения
        $domain = $bot->getDomainForEnvironment($currentEnvironment);
        if (!$domain) {
            $this->error("❌ Домен для окружения '{$currentEnvironment}' не установлен");
            return 1;
        }

        // Формируем webhook URL
        $webhookUrl = rtrim($domain, '/') . "/webhook/{$bot->name}";
        $secret = $bot->webhook_secret;

        $this->info("🌐 Webhook URL: {$webhookUrl}");

        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getWebhookInfo");

            if ($response->successful()) {
                $info = $response->json()['result'];
                $this->displayWebhookInfo($info);

                $this->line("🌍 Текущее окружение: {$currentEnvironment}");
                $this->line("🌐 Webhook URL: {$webhookUrl}");

                if ($info['url'] === $webhookUrl) {
                    $this->info('✅ Webhook соответствует текущему окружению');
                } else {
                    $this->warn('⚠️  Webhook URL не соответствует текущему окружению. Ожидалось: ' . $webhookUrl);
                }

                if ($info['secret_token'] === $secret) {
                    $this->info('✅ Secret token соответствует текущему окружению');
                } else {
                    $this->warn('⚠️  Secret token не соответствует текущему окружению. Ожидалось: ' . $secret);
                }

                if ($info['max_connections'] === 40) {
                    $this->info('✅ Максимальное количество соединений соответствует текущему окружению');
                } else {
                    $this->warn('⚠️  Максимальное количество соединений не соответствует текущему окружению. Ожидалось: 40');
                }

                if ($info['allowed_updates'] === ['message', 'callback_query', 'inline_query', 'chosen_inline_result', 'channel_post', 'edited_message', 'edited_channel_post']) {
                    $this->info('✅ Разрешенные обновления соответствуют текущему окружению');
                } else {
                    $this->warn('⚠️  Разрешенные обновления не соответствуют текущему окружению. Ожидалось: message, callback_query, inline_query, chosen_inline_result, channel_post, edited_message, edited_channel_post');
                }

            } else {
                $this->error('❌ Ошибка получения информации о webhook для проверки');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка проверки webhook: ' . $e->getMessage());
            return 1;
        }

        return 0;
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

    private function displayWebhookInfo(array $info): void
    {
        $this->info('📋 Информация о webhook:');
        $this->newLine();

        if ($info['url']) {
            $this->line("🌐 URL: {$info['url']}");
            $this->line("🔐 Secret: " . ($info['has_custom_certificate'] ? 'Да' : 'Нет'));
            $this->line("📊 Макс. соединений: {$info['max_connections']}");
            $this->line("📅 Последняя ошибка: " . ($info['last_error_date'] ? date('d.m.Y H:i:s', $info['last_error_date']) : 'Нет'));
            
            if ($info['last_error_message']) {
                $this->line("❌ Сообщение об ошибке: {$info['last_error_message']}");
            }

            $this->line("📈 Ожидающие обновления: {$info['pending_update_count']}");
        } else {
            $this->line("❌ Webhook не настроен");
        }

        $this->newLine();
    }

    private function checkSSL(string $url): void
    {
        $this->line('🔒 Проверка SSL сертификата...');

        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ]
            ]);

            $host = parse_url($url, PHP_URL_HOST);
            $port = parse_url($url, PHP_URL_PORT) ?: 443;

            $socket = stream_socket_client(
                "ssl://{$host}:{$port}",
                $errno,
                $errstr,
                10,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if ($socket) {
                $this->info('✅ SSL сертификат валиден');
                fclose($socket);
            } else {
                $this->error("❌ Ошибка SSL: {$errstr} ({$errno})");
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка проверки SSL: ' . $e->getMessage());
        }
    }

    private function sendTestUpdate(string $webhookUrl, string $secret): void
    {
        $this->line('📤 Отправка тестового обновления...');

        $testUpdate = [
            'update_id' => 123456789,
            'message' => [
                'message_id' => 1,
                'from' => [
                    'id' => 123456789,
                    'is_bot' => false,
                    'first_name' => 'Test',
                    'username' => 'testuser'
                ],
                'chat' => [
                    'id' => 123456789,
                    'first_name' => 'Test',
                    'username' => 'testuser',
                    'type' => 'private'
                ],
                'date' => time(),
                'text' => '/test'
            ]
        ];

        try {
            $headers = ['Content-Type' => 'application/json'];
            if ($secret) {
                $headers['X-Telegram-Bot-Api-Secret-Token'] = $secret;
            }

            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->post($webhookUrl, $testUpdate);

            if ($response->successful()) {
                $this->info('✅ Тестовое обновление отправлено успешно');
            } else {
                $this->warn("⚠️  Ответ сервера: {$response->status()}");
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка отправки тестового обновления: ' . $e->getMessage());
        }
    }
} 