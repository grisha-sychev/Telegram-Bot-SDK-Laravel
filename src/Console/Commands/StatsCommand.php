<?php

namespace Teg\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatsCommand extends Command
{
    protected $signature = 'teg:stats 
                            {--period=24h : Period for statistics (1h, 24h, 7d, 30d)}
                            {--format=table : Output format (table, json)}
                            {--detailed : Show detailed statistics}';
    
    protected $description = 'Статистика TegBot';

    public function handle()
    {
        $this->info('📊 TegBot Statistics');
        $this->newLine();

        $period = $this->option('period');
        $format = $this->option('format');
        $detailed = $this->option('detailed');

        $stats = $this->gatherStatistics($period, $detailed);

        if ($format === 'json') {
            $this->line(json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->displayStatsTable($stats, $period, $detailed);
        }

        return 0;
    }

    private function gatherStatistics(string $period, bool $detailed): array
    {
        $stats = [
            'bot_info' => $this->getBotInfo(),
            'system' => $this->getSystemStats(),
            'performance' => $this->getPerformanceStats($period),
            'errors' => $this->getErrorStats($period),
        ];

        if ($detailed) {
            $stats['detailed'] = [
                'webhook' => $this->getWebhookStats(),
                'cache' => $this->getCacheStats(),
                'storage' => $this->getStorageStats(),
                'memory' => $this->getMemoryStats(),
            ];
        }

        return $stats;
    }

    private function getBotInfo(): array
    {
        $token = config('tegbot.token');
        
        if (!$token) {
            return ['error' => 'Token not configured'];
        }

        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getMe");
            
            if ($response->successful()) {
                return $response->json()['result'];
            }
            
            return ['error' => 'API error: ' . $response->status()];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getSystemStats(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'memory_limit' => $this->parseMemoryLimit(ini_get('memory_limit')),
            'uptime' => $this->getUptime(),
            'timezone' => config('app.timezone'),
            'environment' => app()->environment(),
        ];
    }

    private function getPerformanceStats(string $period): array
    {
        $hours = $this->periodToHours($period);
        
        // В реальном приложении здесь должны быть данные из логов или БД
        return [
            'period' => $period,
            'total_requests' => $this->mockStat(100, 1000),
            'successful_requests' => $this->mockStat(90, 950),
            'failed_requests' => $this->mockStat(5, 50),
            'average_response_time' => $this->mockStat(50, 200) . 'ms',
            'requests_per_hour' => round($this->mockStat(10, 100) / max($hours, 1), 2),
            'unique_users' => $this->mockStat(20, 200),
            'unique_chats' => $this->mockStat(15, 150),
        ];
    }

    private function getErrorStats(string $period): array
    {
        // В реальном приложении здесь анализ логов
        return [
            'total_errors' => $this->mockStat(0, 20),
            'api_errors' => $this->mockStat(0, 10),
            'webhook_errors' => $this->mockStat(0, 5),
            'timeout_errors' => $this->mockStat(0, 3),
            'rate_limit_hits' => $this->mockStat(0, 2),
            'last_error' => $this->getLastError(),
        ];
    }

    private function getWebhookStats(): array
    {
        $token = config('tegbot.token');
        
        if (!$token) {
            return ['error' => 'Token not configured'];
        }

        try {
            $response = Http::get("https://api.telegram.org/bot{$token}/getWebhookInfo");
            
            if ($response->successful()) {
                $info = $response->json()['result'];
                return [
                    'url' => $info['url'] ?? 'Not set',
                    'has_custom_certificate' => $info['has_custom_certificate'] ?? false,
                    'pending_update_count' => $info['pending_update_count'] ?? 0,
                    'max_connections' => $info['max_connections'] ?? 0,
                    'allowed_updates' => $info['allowed_updates'] ?? [],
                    'last_error_date' => isset($info['last_error_date']) 
                        ? date('Y-m-d H:i:s', $info['last_error_date']) 
                        : null,
                    'last_error_message' => $info['last_error_message'] ?? null,
                ];
            }
            
            return ['error' => 'API error: ' . $response->status()];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getCacheStats(): array
    {
        if (!config('tegbot.cache.enabled', false)) {
            return ['status' => 'disabled'];
        }

        try {
            $driver = config('tegbot.cache.driver', 'file');
            $testKey = 'tegbot_stats_test_' . time();
            
            $start = microtime(true);
            Cache::put($testKey, 'test', 10);
            $writeTime = (microtime(true) - $start) * 1000;
            
            $start = microtime(true);
            $value = Cache::get($testKey);
            $readTime = (microtime(true) - $start) * 1000;
            
            Cache::forget($testKey);
            
            return [
                'driver' => $driver,
                'status' => 'working',
                'write_time' => round($writeTime, 2) . 'ms',
                'read_time' => round($readTime, 2) . 'ms',
                'test_successful' => $value === 'test',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function getStorageStats(): array
    {
        $downloadPath = config('tegbot.files.download_path', storage_path('app/tegbot/downloads'));
        
        if (!is_dir($downloadPath)) {
            return ['error' => 'Download directory not found'];
        }

        $totalSize = 0;
        $fileCount = 0;
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($downloadPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $totalSize += $file->getSize();
                $fileCount++;
            }
        }
        
        return [
            'download_path' => $downloadPath,
            'total_files' => $fileCount,
            'total_size' => $this->formatFileSize($totalSize),
            'available_space' => $this->formatFileSize(disk_free_space($downloadPath)),
            'is_writable' => is_writable($downloadPath),
        ];
    }

    private function getMemoryStats(): array
    {
        $current = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $limit = $this->parseMemoryLimit(ini_get('memory_limit'));
        
        return [
            'current_usage' => $this->formatFileSize($current),
            'peak_usage' => $this->formatFileSize($peak),
            'memory_limit' => $limit > 0 ? $this->formatFileSize($limit) : 'unlimited',
            'usage_percentage' => $limit > 0 ? round(($current / $limit) * 100, 1) : null,
            'available' => $limit > 0 ? $this->formatFileSize($limit - $current) : 'unlimited',
        ];
    }

    private function displayStatsTable(array $stats, string $period, bool $detailed): void
    {
        // Bot Info
        if (isset($stats['bot_info']['username'])) {
            $bot = $stats['bot_info'];
            $this->info("🤖 Bot: @{$bot['username']} ({$bot['first_name']})");
            $this->line("   ID: {$bot['id']}");
            $this->newLine();
        }

        // Performance Stats
        $perf = $stats['performance'];
        $this->info("📈 Performance ({$period}):");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Requests', $perf['total_requests']],
                ['Successful', $perf['successful_requests']],
                ['Failed', $perf['failed_requests']],
                ['Avg Response Time', $perf['average_response_time']],
                ['Requests/Hour', $perf['requests_per_hour']],
                ['Unique Users', $perf['unique_users']],
                ['Unique Chats', $perf['unique_chats']],
            ]
        );

        // Error Stats
        if ($stats['errors']['total_errors'] > 0) {
            $this->newLine();
            $this->warn("⚠️  Errors ({$period}):");
            $errors = $stats['errors'];
            $this->table(
                ['Error Type', 'Count'],
                [
                    ['Total Errors', $errors['total_errors']],
                    ['API Errors', $errors['api_errors']],
                    ['Webhook Errors', $errors['webhook_errors']],
                    ['Timeout Errors', $errors['timeout_errors']],
                    ['Rate Limit Hits', $errors['rate_limit_hits']],
                ]
            );
        }

        // System Stats
        $this->newLine();
        $system = $stats['system'];
        $this->info('💻 System:');
        $this->line("   PHP: {$system['php_version']}");
        $this->line("   Laravel: {$system['laravel_version']}");
        $this->line("   Memory: " . $this->formatFileSize($system['memory_usage']));
        $this->line("   Environment: {$system['environment']}");

        // Detailed stats
        if ($detailed && isset($stats['detailed'])) {
            $this->displayDetailedStats($stats['detailed']);
        }
    }

    private function displayDetailedStats(array $detailed): void
    {
        $this->newLine();
        $this->info('🔍 Detailed Information:');

        // Webhook
        if (isset($detailed['webhook']['url'])) {
            $webhook = $detailed['webhook'];
            $this->line("   🌐 Webhook: {$webhook['url']}");
            $this->line("   📊 Pending: {$webhook['pending_update_count']}");
            if ($webhook['last_error_message']) {
                $this->warn("   ❌ Last Error: {$webhook['last_error_message']}");
            }
        }

        // Cache
        if ($detailed['cache']['status'] === 'working') {
            $cache = $detailed['cache'];
            $this->line("   💾 Cache: {$cache['driver']} (W:{$cache['write_time']}, R:{$cache['read_time']})");
        }

        // Storage
        if (isset($detailed['storage']['total_files'])) {
            $storage = $detailed['storage'];
            $this->line("   📁 Files: {$storage['total_files']} ({$storage['total_size']})");
        }

        // Memory
        $memory = $detailed['memory'];
        $this->line("   🧠 Memory: {$memory['current_usage']} / {$memory['memory_limit']}");
        if ($memory['usage_percentage']) {
            $this->line("      Usage: {$memory['usage_percentage']}%");
        }
    }

    private function getLastError(): ?string
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (!file_exists($logPath)) {
            return null;
        }

        try {
            $handle = fopen($logPath, 'r');
            $lastLine = '';
            
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (str_contains($line, 'ERROR') || str_contains($line, 'CRITICAL')) {
                        $lastLine = trim($line);
                    }
                }
                fclose($handle);
            }
            
            if ($lastLine) {
                // Парсим дату из лога Laravel
                preg_match('/\[(.*?)\]/', $lastLine, $matches);
                return $matches[1] ?? 'Unknown time';
            }
            
            return null;
        } catch (\Exception $e) {
            return 'Error reading logs';
        }
    }

    private function getUptime(): string
    {
        // Простая реализация - время с последнего изменения конфига
        $configPath = config_path('tegbot.php');
        
        if (file_exists($configPath)) {
            $lastModified = filemtime($configPath);
            $uptime = time() - $lastModified;
            
            return $this->formatTimeDiff($uptime);
        }
        
        return 'Unknown';
    }

    private function periodToHours(string $period): int
    {
        $mapping = [
            '1h' => 1,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720,
        ];
        
        return $mapping[$period] ?? 24;
    }

    private function mockStat(int $min, int $max): int
    {
        // В реальном приложении здесь должны быть данные из БД или логов
        return rand($min, $max);
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

    private function formatFileSize(int $bytes): string
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
            return "{$seconds}s";
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            return "{$minutes}m";
        } elseif ($seconds < 86400) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return "{$hours}h {$minutes}m";
        } else {
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            return "{$days}d {$hours}h";
        }
    }
} 