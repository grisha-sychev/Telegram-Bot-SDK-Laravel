<?php

namespace Teg\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HealthCommand extends Command
{
    protected $signature = 'teg:health';
    protected $description = 'Проверка состояния TegBot';

    public function handle()
    {
        $this->info('🔍 Checking bot health...');
        $this->newLine();

        $botToken = config('tegbot.token', env('TEGBOT_TOKEN'));
        
        if (!$botToken) {
            $this->error('❌ TEGBOT_TOKEN не настроен');
            return 1;
        }

        // Проверяем API
        $apiStatus = $this->checkTelegramAPI($botToken);
        $this->displayApiStatus($apiStatus);

        // Проверяем конфигурацию
        $this->checkConfiguration();

        // Проверяем хранилище
        $this->checkStorage();

        // Проверяем состояние системы
        $this->checkSystemHealth();

        $this->newLine();
        $this->info('✅ Проверка завершена');

        return 0;
    }

    private function checkTelegramAPI(string $token): array
    {
        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getMe");
            
            if ($response->successful()) {
                $botInfo = $response->json()['result'];
                return [
                    'status' => 'ok',
                    'bot_info' => $botInfo,
                ];
            }
            
            return [
                'status' => 'error',
                'message' => 'API returned: ' . $response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function displayApiStatus(array $status): void
    {
        if ($status['status'] === 'ok') {
            $bot = $status['bot_info'];
            $this->info("🤖 Checking bot: {$bot['username']}");
            $this->line("  📝 Bot Name: {$bot['first_name']}");
            $this->line("  🆔 Bot Username: @{$bot['username']}");
            $this->line("  📡 Can receive all updates: " . ($bot['can_read_all_group_messages'] ? 'Yes' : 'No'));
            
            // Проверяем webhook
            $this->checkWebhook();
        } else {
            $this->error("❌ Telegram API: {$status['message']}");
        }
    }

    private function checkWebhook(): void
    {
        try {
            $token = config('tegbot.token', env('TEGBOT_TOKEN'));
            $response = Http::get("https://api.telegram.org/bot{$token}/getWebhookInfo");
            
            if ($response->successful()) {
                $webhook = $response->json()['result'];
                
                if ($webhook['url']) {
                    $this->line("  🌐 Webhook URL: {$webhook['url']}");
                    $this->line("  📊 Pending Updates: {$webhook['pending_update_count']}");
                    
                    if (!empty($webhook['last_error_message'])) {
                        $errorDate = date('Y-m-d H:i:s', $webhook['last_error_date']);
                        $this->warn("  ⚠️  Last Error: {$errorDate} - {$webhook['last_error_message']}");
                    }
                } else {
                    $this->warn('  ⚠️  Webhook not set');
                }
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Webhook check failed: {$e->getMessage()}");
        }
    }

    private function checkConfiguration(): void
    {
        $this->line("  🔧 **Configuration:**");
        
        $adminIds = config('tegbot.security.admin_ids', []);
        if (empty($adminIds)) {
            $this->warn('    ⚠️  No admin IDs configured');
        } else {
            $this->line('    ✅ Admin IDs: ' . count($adminIds) . ' configured');
        }

        $webhookSecret = config('tegbot.security.webhook_secret');
        if (empty($webhookSecret)) {
            $this->warn('    ⚠️  Webhook secret not set (security risk)');
        } else {
            $this->line('    ✅ Webhook secret: configured');
        }

        $logging = config('tegbot.logging.enabled', false);
        $this->line('    📊 Detailed Logging: ' . ($logging ? 'ON' : 'OFF'));

        $fileStorage = config('tegbot.files.download_path', 'storage/app/tegbot');
        $this->line("    📁 File Storage: " . basename($fileStorage));

        $timeout = config('tegbot.api.timeout', 30);
        $this->line("    ⏱️  API Timeout: {$timeout}s");

        $retries = config('tegbot.api.retries', 3);
        $this->line("    🔄 Rate Limit Retries: {$retries}");
    }

    private function checkStorage(): void
    {
        $downloadPath = config('tegbot.files.download_path', storage_path('app/tegbot/downloads'));
        
        if (!is_dir($downloadPath)) {
            try {
                mkdir($downloadPath, 0755, true);
                $this->line('  ✅ Storage directory created: ' . basename(dirname($downloadPath)));
            } catch (\Exception $e) {
                $this->error("  ❌ Cannot create storage directory: {$e->getMessage()}");
                return;
            }
        } else {
            $this->line('  ✅ Storage directory exists: ' . basename(dirname($downloadPath)));
        }

        if (!is_writable($downloadPath)) {
            $this->error('  ❌ Storage directory is not writable');
        } else {
            $this->line('  ✅ Storage directory is writable');
        }
    }

    private function checkSystemHealth(): void
    {
        $this->newLine();
        $this->line('🏥 **System Health:**');

        // Проверка памяти
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        
        if ($memoryLimit > 0) {
            $percentage = round(($memoryUsage / $memoryLimit) * 100, 1);
            $this->line("  💾 Memory: " . $this->formatBytes($memoryUsage) . " / " . $this->formatBytes($memoryLimit) . " ({$percentage}%)");
            
            if ($percentage > 80) {
                $this->warn('    ⚠️  High memory usage detected');
            }
        } else {
            $this->line("  💾 Memory: " . $this->formatBytes($memoryUsage) . " (no limit)");
        }

        // Проверка Redis (если используется)
        if (config('tegbot.cache.enabled') && config('tegbot.cache.driver') === 'redis') {
            try {
                Cache::store('redis')->put('tegbot_health_test', 'ok', 10);
                $test = Cache::store('redis')->get('tegbot_health_test');
                
                if ($test === 'ok') {
                    $this->line('  🔴 Redis: Connected');
                } else {
                    $this->warn('  ⚠️  Redis: Connection issues');
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Redis: {$e->getMessage()}");
            }
        }

        // Последняя активность (если есть логи)
        $this->checkLastActivity();
    }

    private function checkLastActivity(): void
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (file_exists($logPath)) {
            $lastModified = filemtime($logPath);
            $timeDiff = time() - $lastModified;
            
            if ($timeDiff < 300) { // 5 минут
                $this->line('  ⚡ Last Activity: ' . $this->formatTimeDiff($timeDiff) . ' ago');
            } else {
                $this->warn('  ⚠️  Last Activity: ' . $this->formatTimeDiff($timeDiff) . ' ago');
            }
        }
    }

    private function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1') return 0;
        
        $limit = trim($limit);
        $bytes = (int) $limit;
        
        if (preg_match('/(\d+)(.)/', $limit, $matches)) {
            $bytes = (int) $matches[1];
            switch (strtoupper($matches[2])) {
                case 'G': $bytes *= 1024;
                case 'M': $bytes *= 1024;
                case 'K': $bytes *= 1024;
            }
        }
        
        return $bytes;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 1) . ' ' . $units[$i];
    }

    private function formatTimeDiff(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds} seconds";
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            return "{$minutes} minutes";
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return "{$hours}h {$minutes}m";
        }
    }
} 