<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebhookCommand extends Command
{
    protected $signature = 'bot:webhook 
                            {action : Action (set, info, delete, test)}
                            {url? : Webhook URL (for set action)}
                            {--secret= : Webhook secret token}
                            {--max-connections=40 : Max webhook connections}
                            {--no-ssl : Disable SSL verification}
                            {--force : Force action without confirmation}';
    
    protected $description = 'Управление webhook TegBot';

    public function handle()
    {
        $action = $this->argument('action');
        $token = config('tegbot.token', env('TEGBOT_TOKEN'));

        if (!$token) {
            $this->error('❌ TEGBOT_TOKEN не настроен');
            return 1;
        }

        switch ($action) {
            case 'set':
                return $this->setWebhook($token);
            case 'info':
                return $this->getWebhookInfo($token);
            case 'delete':
                return $this->deleteWebhook($token);
            case 'test':
                return $this->testWebhook($token);
            default:
                $this->error("Неизвестное действие: {$action}");
                $this->line('Доступные действия: set, info, delete, test');
                return 1;
        }
    }

    private function setWebhook(string $token): int
    {
        $url = $this->argument('url');
        
        if (!$url) {
            $url = $this->ask('Введите URL webhook');
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

        if (!str_starts_with($url, 'https://')) {
            $this->error('❌ URL должен использовать HTTPS');
            return 1;
        }

        // Подготавливаем параметры
        $secret = $this->option('secret') ?? config('tegbot.security.webhook_secret', env('TEGBOT_WEBHOOK_SECRET'));
        $maxConnections = $this->option('max-connections');

        if (!$secret) {
            if ($this->confirm('Генерировать webhook secret автоматически?', true)) {
                $secret = Str::random(32);
                $this->warn("💡 Сгенерирован secret: {$secret}");
                $this->warn('Добавьте в .env файл:');
                $this->line("TEGBOT_WEBHOOK_SECRET={$secret}");
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

        // Показываем что будем делать
        $this->info('🌐 Установка webhook:');
        $this->line("  URL: {$url}");
        $this->line("  Max connections: {$maxConnections}");
        $this->line("  Secret: " . ($secret ? 'Да' : 'Нет'));
        $this->line("  Updates: " . count($payload['allowed_updates']) . " типов");

        if (!$this->option('force') && !$this->confirm('Продолжить установку?', true)) {
            $this->info('Отменено');
            return 0;
        }

        // Выполняем запрос
        try {
            $http = Http::timeout(30);
            
            if ($this->option('no-ssl')) {
                $http = $http->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ]
                ]);
                $this->warn('⚠️  SSL проверка отключена');
            }
            
            $response = $http->post("https://api.telegram.org/bot{$token}/setWebhook", $payload);
            
            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['ok']) {
                    $this->info('✅ Webhook установлен успешно');
                    
                    // Проверяем сразу
                    $this->newLine();
                    $this->info('🔍 Проверка webhook...');
                    $this->getWebhookInfo($token);
                } else {
                    $this->error('❌ Ошибка: ' . ($result['description'] ?? 'Unknown error'));
                    return 1;
                }
            } else {
                $this->error('❌ HTTP ошибка: ' . $response->status());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка соединения: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function getWebhookInfo(string $token): int
    {
        try {
            $http = Http::timeout(10);
            
            if ($this->option('no-ssl')) {
                $http = $http->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ]
                ]);
            }
            
            $response = $http->get("https://api.telegram.org/bot{$token}/getWebhookInfo");
            
            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['ok']) {
                    $info = $result['result'];
                    $this->displayWebhookInfo($info);
                } else {
                    $this->error('❌ Ошибка: ' . ($result['description'] ?? 'Unknown error'));
                    return 1;
                }
            } else {
                $this->error('❌ HTTP ошибка: ' . $response->status());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка соединения: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function deleteWebhook(string $token): int
    {
        if (!$this->option('force') && !$this->confirm('⚠️  Удалить webhook?', false)) {
            $this->info('Отменено');
            return 0;
        }

        try {
            $http = Http::timeout(10);
            
            if ($this->option('no-ssl')) {
                $http = $http->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ]
                ]);
            }
            
            $response = $http->post("https://api.telegram.org/bot{$token}/deleteWebhook");
            
            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['ok']) {
                    $this->info('✅ Webhook удален');
                } else {
                    $this->error('❌ Ошибка: ' . ($result['description'] ?? 'Unknown error'));
                    return 1;
                }
            } else {
                $this->error('❌ HTTP ошибка: ' . $response->status());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Ошибка соединения: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function testWebhook(string $token): int
    {
        $this->info('🧪 Тестирование webhook...');
        $this->newLine();

        // Получаем информацию о webhook
        try {
            $http = Http::timeout(10);
            
            if ($this->option('no-ssl')) {
                $http = $http->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ]
                ]);
            }
            
            $response = $http->get("https://api.telegram.org/bot{$token}/getWebhookInfo");
            
            if (!$response->successful()) {
                $this->error('❌ Не удалось получить информацию о webhook');
                return 1;
            }

            $result = $response->json();
            $info = $result['result'];

            if (empty($info['url'])) {
                $this->error('❌ Webhook не установлен');
                return 1;
            }

            $webhookUrl = $info['url'];
            $this->info("🌐 Тестируем: {$webhookUrl}");

            // Проверка 1: HTTP доступность
            $this->line('🔍 Проверка доступности...');
            
            try {
                $testHttp = Http::timeout(10);
                
                if ($this->option('no-ssl')) {
                    $testHttp = $testHttp->withOptions([
                        'verify' => false,
                        'curl' => [
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                        ]
                    ]);
                }
                
                $testResponse = $testHttp->get($webhookUrl);
                $this->info("  ✅ HTTP статус: {$testResponse->status()}");
            } catch (\Exception $e) {
                $this->warn("  ⚠️  HTTP недоступен: {$e->getMessage()}");
            }

            // Проверка 2: SSL сертификат
            $this->line('🔍 Проверка SSL...');
            $this->checkSSL($webhookUrl);

            // Проверка 3: Статистика webhook
            $this->newLine();
            $this->line('📊 Статистика webhook:');
            $this->displayWebhookInfo($info);

            // Проверка 4: Отправка тестового запроса (если есть secret)
            $secret = config('tegbot.security.webhook_secret', env('TEGBOT_WEBHOOK_SECRET'));
            if ($secret) {
                $this->newLine();
                $this->line('🧪 Отправка тестового запроса...');
                $this->sendTestUpdate($webhookUrl, $secret);
            }

        } catch (\Exception $e) {
            $this->error('❌ Ошибка тестирования: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function displayWebhookInfo(array $info): void
    {
        if (empty($info['url'])) {
            $this->warn('⚠️  Webhook не установлен');
            return;
        }

        $this->info('🌐 Webhook Information:');
        $this->table(
            ['Parameter', 'Value'],
            [
                ['URL', $info['url']],
                ['Has Custom Certificate', $info['has_custom_certificate'] ? 'Yes' : 'No'],
                ['Pending Updates', $info['pending_update_count'] ?? 0],
                ['Max Connections', $info['max_connections'] ?? 'Default'],
                ['Allowed Updates', empty($info['allowed_updates']) ? 'All' : implode(', ', $info['allowed_updates'])],
                ['Last Error Date', isset($info['last_error_date']) ? date('Y-m-d H:i:s', $info['last_error_date']) : 'None'],
                ['Last Error Message', $info['last_error_message'] ?? 'None'],
                ['Last Synchronization Error Date', isset($info['last_synchronization_error_date']) ? date('Y-m-d H:i:s', $info['last_synchronization_error_date']) : 'None'],
            ]
        );

        // Анализ состояния
        if ($info['pending_update_count'] > 100) {
            $this->warn("⚠️  Большое количество необработанных обновлений: {$info['pending_update_count']}");
        }

        if (!empty($info['last_error_message'])) {
            $errorAge = time() - ($info['last_error_date'] ?? 0);
            if ($errorAge < 3600) { // Меньше часа
                $this->error("🚨 Недавняя ошибка: {$info['last_error_message']}");
            }
        }
    }

    private function checkSSL(string $url): void
    {
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT) ?? 443;

        try {
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ]
            ]);

            $socket = stream_socket_client(
                "ssl://{$host}:{$port}",
                $errno,
                $errstr,
                10,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if ($socket) {
                $cert = stream_context_get_params($socket)['options']['ssl']['peer_certificate'];
                $certInfo = openssl_x509_parse($cert);
                
                $validFrom = date('Y-m-d', $certInfo['validFrom_time_t']);
                $validTo = date('Y-m-d', $certInfo['validTo_time_t']);
                $daysLeft = floor(($certInfo['validTo_time_t'] - time()) / 86400);
                
                $this->info("  ✅ SSL сертификат действителен");
                $this->line("     Действует: {$validFrom} - {$validTo}");
                
                if ($daysLeft < 30) {
                    $this->warn("     ⚠️  Сертификат истекает через {$daysLeft} дней");
                } else {
                    $this->line("     📅 Осталось: {$daysLeft} дней");
                }
                
                fclose($socket);
            } else {
                $this->error("  ❌ SSL соединение неудачно: {$errstr}");
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Ошибка проверки SSL: {$e->getMessage()}");
        }
    }

    private function sendTestUpdate(string $webhookUrl, string $secret): void
    {
        // Создаем тестовое обновление
        $testUpdate = [
            'update_id' => 999999999,
            'message' => [
                'message_id' => 999999,
                'date' => time(),
                'text' => '/test_webhook_' . time(),
                'from' => [
                    'id' => 999999999,
                    'is_bot' => false,
                    'first_name' => 'TegBot',
                    'username' => 'tegbot_test'
                ],
                'chat' => [
                    'id' => 999999999,
                    'type' => 'private',
                    'first_name' => 'TegBot',
                    'username' => 'tegbot_test'
                ]
            ]
        ];

        try {
            $http = Http::timeout(10)
                ->withHeaders([
                    'X-Telegram-Bot-Api-Secret-Token' => $secret,
                    'Content-Type' => 'application/json'
                ]);
            
            if ($this->option('no-ssl')) {
                $http = $http->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ]
                ]);
            }
            
            $response = $http->post($webhookUrl, $testUpdate);

            $this->info("  📤 Отправлен тестовый запрос");
            $this->line("  📥 Ответ: HTTP {$response->status()}");
            
            if ($response->successful()) {
                $this->info("  ✅ Webhook принял запрос");
            } else {
                $this->warn("  ⚠️  Неожиданный статус ответа");
            }
            
        } catch (\Exception $e) {
            $this->error("  ❌ Ошибка отправки: {$e->getMessage()}");
        }
    }
} 