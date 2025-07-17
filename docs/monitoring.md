# 📊 Мониторинг TegBot

## Обзор

TegBot предоставляет комплексные инструменты для мониторинга:

- 🔍 **Проверка здоровья**: Диагностика состояния бота
- 📈 **Метрики**: Детальная статистика работы
- 📝 **Логирование**: Структурированные логи событий
- 🚨 **Алерты**: Автоматические уведомления о проблемах
- 📊 **Дашборды**: Визуализация данных
- 🛠️ **Отладка**: Инструменты для диагностики

## Проверка здоровья бота

### Команда health check

```bash
php artisan teg:health
```

Результат проверки:
```
🏥 TegBot Health Check Report

✅ Telegram API: Подключение успешно (150ms)
✅ Конфигурация бота: Настроен корректно
✅ Webhook: Активен и работает
✅ База данных: Соединение установлено
✅ Кэш: Redis доступен
✅ Хранилище файлов: Доступно для записи
✅ Память: 45MB / 128MB (35%)
✅ Последняя активность: 2 минуты назад

⚠️  Предупреждения:
- Последняя ошибка: 1 час назад (HTTP 429 - Rate limit)
- Высокая нагрузка на диск: 85%

📊 Статистика за последние 24 часа:
- Обработано сообщений: 1,247
- Выполнено команд: 389
- Ошибок API: 12
- Активных пользователей: 156
```

### Программная проверка

```php
class BotHealthChecker
{
    public function checkHealth(): array
    {
        return [
            'api' => $this->checkTelegramAPI(),
            'webhook' => $this->checkWebhook(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'memory' => $this->checkMemory(),
            'activity' => $this->checkRecentActivity(),
            'errors' => $this->getRecentErrors(),
        ];
    }
    
    private function checkTelegramAPI(): array
    {
        $startTime = microtime(true);
        
        try {
            $response = Http::timeout(10)->get(
                "https://api.telegram.org/bot" . config('tegbot.token') . "/getMe"
            );
            
            $responseTime = round((microtime(true) - $startTime) * 1000);
            
            if ($response->successful()) {
                return [
                    'status' => 'ok',
                    'response_time' => $responseTime . 'ms',
                    'bot_info' => $response->json()['result'],
                ];
            }
            
            return [
                'status' => 'error',
                'message' => 'API недоступен: ' . $response->status(),
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка соединения: ' . $e->getMessage(),
            ];
        }
    }
    
    private function checkWebhook(): array
    {
        try {
            $response = Http::get(
                "https://api.telegram.org/bot" . config('tegbot.token') . "/getWebhookInfo"
            );
            
            if ($response->successful()) {
                $info = $response->json()['result'];
                
                return [
                    'status' => $info['url'] ? 'active' : 'inactive',
                    'url' => $info['url'] ?? null,
                    'pending_updates' => $info['pending_update_count'] ?? 0,
                    'last_error' => $info['last_error_message'] ?? null,
                    'last_error_date' => $info['last_error_date'] ?? null,
                ];
            }
            
            return ['status' => 'error', 'message' => 'Не удалось получить информацию'];
            
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    private function checkMemory(): array
    {
        $used = memory_get_usage(true);
        $limit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $percentage = $limit > 0 ? round(($used / $limit) * 100, 1) : 0;
        
        return [
            'used' => $this->formatBytes($used),
            'limit' => $this->formatBytes($limit),
            'percentage' => $percentage,
            'status' => $percentage > 80 ? 'warning' : 'ok',
        ];
    }
}
```

## Логирование

### Структура логов

```php
// Логирование активности
$this->logActivity('user_interaction', [
    'type' => 'command',
    'command' => '/start',
    'user_id' => 123456789,
    'chat_id' => -1001234567890,
    'chat_type' => 'private',
    'timestamp' => now()->toISOString(),
    'response_time_ms' => 150,
    'memory_usage' => memory_get_usage(true),
]);

// Логирование ошибок
$this->logError('API Request Failed', $exception, [
    'method' => 'sendMessage',
    'endpoint' => 'https://api.telegram.org/bot{token}/sendMessage',
    'user_id' => 123456789,
    'attempt' => 2,
    'http_code' => 429,
]);
```

### Настройка логирования

```php
// config/tegbot.php
'logging' => [
    'enabled' => true,
    'level' => env('TEGBOT_LOG_LEVEL', 'info'),
    'channels' => [
        'default' => 'stack',
        'errors' => 'daily',
        'activity' => 'tegbot_activity',
        'security' => 'tegbot_security',
    ],
    'max_entries' => 10000,
    'retention_days' => 30,
    'structured_logs' => true,
],
```

### Кастомные логгеры

```php
// config/logging.php
'channels' => [
    'tegbot_activity' => [
        'driver' => 'daily',
        'path' => storage_path('logs/tegbot/activity.log'),
        'level' => 'info',
        'days' => 14,
        'formatter' => \App\Logging\TegBotFormatter::class,
    ],
    
    'tegbot_security' => [
        'driver' => 'daily', 
        'path' => storage_path('logs/tegbot/security.log'),
        'level' => 'warning',
        'days' => 90,
    ],
],
```

## Метрики и статистика

### Сбор метрик

```php
class BotMetrics
{
    public function collectMetrics(): array
    {
        $now = now();
        $dayAgo = $now->copy()->subDay();
        $weekAgo = $now->copy()->subWeek();
        
        return [
            'messages' => [
                'last_24h' => $this->getMessageCount($dayAgo, $now),
                'last_week' => $this->getMessageCount($weekAgo, $now),
                'by_type' => $this->getMessagesByType($dayAgo, $now),
            ],
            'users' => [
                'active_24h' => $this->getActiveUsersCount($dayAgo, $now),
                'new_users' => $this->getNewUsersCount($dayAgo, $now),
                'total_users' => $this->getTotalUsersCount(),
            ],
            'commands' => [
                'executions' => $this->getCommandExecutions($dayAgo, $now),
                'most_popular' => $this->getMostPopularCommands($dayAgo, $now),
                'errors' => $this->getCommandErrors($dayAgo, $now),
            ],
            'performance' => [
                'avg_response_time' => $this->getAverageResponseTime($dayAgo, $now),
                'error_rate' => $this->getErrorRate($dayAgo, $now),
                'uptime' => $this->getUptime(),
            ],
        ];
    }
    
    private function getMessageCount($from, $to): int
    {
        return DB::table('tegbot_activity_logs')
            ->where('event', 'message_received')
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }
    
    private function getMessagesByType($from, $to): array
    {
        return DB::table('tegbot_activity_logs')
            ->select('data->message_type as type', DB::raw('count(*) as count'))
            ->where('event', 'message_received')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('data->message_type')
            ->pluck('count', 'type')
            ->toArray();
    }
}
```

### Дашборд метрик

```php
$this->registerCommand('stats', function () {
    $this->showBotStats();
}, [
    'description' => 'Показать статистику бота',
    'admin_only' => true,
]);

private function showBotStats(): void
{
    $metrics = app(BotMetrics::class)->collectMetrics();
    
    $message = "📊 **Статистика бота**\n\n";
    
    // Сообщения
    $message .= "📨 **Сообщения (24ч):**\n";
    $message .= "Всего: {$metrics['messages']['last_24h']}\n";
    
    foreach ($metrics['messages']['by_type'] as $type => $count) {
        $icon = $this->getTypeIcon($type);
        $message .= "{$icon} {$type}: {$count}\n";
    }
    
    // Пользователи
    $message .= "\n👥 **Пользователи:**\n";
    $message .= "Активных (24ч): {$metrics['users']['active_24h']}\n";
    $message .= "Новых (24ч): {$metrics['users']['new_users']}\n";
    $message .= "Всего: {$metrics['users']['total_users']}\n";
    
    // Команды
    $message .= "\n⚡ **Команды (24ч):**\n";
    $message .= "Выполнено: {$metrics['commands']['executions']}\n";
    $message .= "Ошибок: {$metrics['commands']['errors']}\n";
    
    $popularCommands = array_slice($metrics['commands']['most_popular'], 0, 5);
    $message .= "\nТоп-5 команд:\n";
    foreach ($popularCommands as $cmd => $count) {
        $message .= "/{$cmd}: {$count}\n";
    }
    
    // Производительность
    $message .= "\n🚀 **Производительность:**\n";
    $message .= "Среднее время ответа: {$metrics['performance']['avg_response_time']}ms\n";
    $message .= "Процент ошибок: {$metrics['performance']['error_rate']}%\n";
    $message .= "Uptime: {$metrics['performance']['uptime']}%\n";
    
    $this->sendSelf($message);
}
```

## Система алертов

### Настройка уведомлений

```php
// config/tegbot.php
'monitoring' => [
    'alerts' => [
        'enabled' => true,
        'channels' => ['telegram', 'email', 'slack'],
        'thresholds' => [
            'error_rate' => 5,        // процент ошибок
            'response_time' => 2000,  // миллисекунды
            'memory_usage' => 80,     // процент
            'disk_usage' => 85,       // процент
        ],
        'cooldown_minutes' => 15,     // минимальный интервал между алертами
    ],
],
```

### Отправка алертов

```php
class AlertManager
{
    public function checkAndSendAlerts(): void
    {
        $metrics = app(BotMetrics::class)->collectMetrics();
        $thresholds = config('tegbot.monitoring.alerts.thresholds');
        
        // Проверка процента ошибок
        if ($metrics['performance']['error_rate'] > $thresholds['error_rate']) {
            $this->sendAlert('high_error_rate', [
                'current' => $metrics['performance']['error_rate'],
                'threshold' => $thresholds['error_rate'],
            ]);
        }
        
        // Проверка времени ответа
        if ($metrics['performance']['avg_response_time'] > $thresholds['response_time']) {
            $this->sendAlert('slow_response', [
                'current' => $metrics['performance']['avg_response_time'],
                'threshold' => $thresholds['response_time'],
            ]);
        }
        
        // Проверка использования памяти
        $memoryUsage = $this->getMemoryUsagePercent();
        if ($memoryUsage > $thresholds['memory_usage']) {
            $this->sendAlert('high_memory_usage', [
                'current' => $memoryUsage,
                'threshold' => $thresholds['memory_usage'],
            ]);
        }
    }
    
    private function sendAlert(string $type, array $data): void
    {
        // Проверяем cooldown
        if (!$this->canSendAlert($type)) {
            return;
        }
        
        $message = $this->formatAlertMessage($type, $data);
        
        // Отправляем в Telegram админам
        $this->sendToAdmins("🚨 {$message}");
        
        // Отправляем по email
        if (config('tegbot.monitoring.alerts.channels.email')) {
            Mail::to(config('tegbot.admin_email'))->send(new BotAlert($type, $data));
        }
        
        // Записываем время отправки алерта
        $this->recordAlertSent($type);
    }
    
    private function formatAlertMessage(string $type, array $data): string
    {
        $messages = [
            'high_error_rate' => "Высокий процент ошибок: {$data['current']}% (лимит: {$data['threshold']}%)",
            'slow_response' => "Медленный ответ: {$data['current']}ms (лимит: {$data['threshold']}ms)",
            'high_memory_usage' => "Высокое использование памяти: {$data['current']}% (лимит: {$data['threshold']}%)",
            'api_down' => "Telegram API недоступен",
            'webhook_failed' => "Webhook не отвечает",
        ];
        
        return $messages[$type] ?? "Неизвестная проблема: {$type}";
    }
}
```

### Автоматические проверки

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Проверка здоровья каждые 5 минут
    $schedule->command('teg:health')
        ->everyFiveMinutes()
        ->withoutOverlapping();
    
    // Проверка алертов каждую минуту
    $schedule->call(function () {
        app(AlertManager::class)->checkAndSendAlerts();
    })->everyMinute();
    
    // Очистка старых логов каждый день
    $schedule->command('teg:logs:cleanup')
        ->daily();
    
    // Еженедельный отчет
    $schedule->call(function () {
        $this->sendWeeklyReport();
    })->weekly();
}
```

## Отладка и диагностика

### Debug режим

```php
// .env
TEGBOT_DEBUG=true
TEGBOT_LOG_LEVEL=debug

// В боте
public function main(): void
{
    if (config('tegbot.debug')) {
        $this->enableDebugMode();
    }
    
    // Остальная логика
}

private function enableDebugMode(): void
{
    // Логируем все входящие сообщения
    $this->globalMiddleware(['debug_logging']);
    
    // Отправляем debug информацию админам
    $this->debugToAdmins([
        'memory' => memory_get_usage(true),
        'time' => microtime(true),
        'request' => request()->all(),
    ]);
}
```

### Детальная диагностика

```php
class BotDebugger
{
    public function diagnose(): array
    {
        return [
            'environment' => $this->getEnvironmentInfo(),
            'configuration' => $this->getConfigurationInfo(),
            'dependencies' => $this->checkDependencies(),
            'permissions' => $this->checkPermissions(),
            'connectivity' => $this->checkConnectivity(),
            'recent_errors' => $this->getRecentErrors(),
        ];
    }
    
    private function getEnvironmentInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'tegbot_version' => $this->getTegBotVersion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'timezone' => config('app.timezone'),
        ];
    }
    
    private function checkDependencies(): array
    {
        $checks = [
            'curl' => extension_loaded('curl'),
            'json' => extension_loaded('json'),
            'openssl' => extension_loaded('openssl'),
            'redis' => class_exists('Redis'),
            'gd' => extension_loaded('gd'),
        ];
        
        return array_map(function ($loaded) {
            return $loaded ? 'OK' : 'Missing';
        }, $checks);
    }
    
    private function checkPermissions(): array
    {
        $paths = [
            'storage/logs' => storage_path('logs'),
            'storage/app/tegbot' => storage_path('app/tegbot'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];
        
        $permissions = [];
        foreach ($paths as $name => $path) {
            $permissions[$name] = [
                'exists' => file_exists($path),
                'readable' => is_readable($path),
                'writable' => is_writable($path),
                'permissions' => $this->getPermissions($path),
            ];
        }
        
        return $permissions;
    }
}
```

### Профилирование производительности

```php
class BotProfiler
{
    private array $timers = [];
    private array $memoryPoints = [];
    
    public function start(string $name): void
    {
        $this->timers[$name] = microtime(true);
        $this->memoryPoints[$name . '_start'] = memory_get_usage(true);
    }
    
    public function end(string $name): array
    {
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $startTime = $this->timers[$name] ?? $endTime;
        $startMemory = $this->memoryPoints[$name . '_start'] ?? $endMemory;
        
        return [
            'duration' => round(($endTime - $startTime) * 1000, 2), // ms
            'memory_used' => $endMemory - $startMemory,
            'peak_memory' => memory_get_peak_usage(true),
        ];
    }
    
    public function profile(string $name, callable $callback)
    {
        $this->start($name);
        $result = $callback();
        $stats = $this->end($name);
        
        // Логируем профилирование
        Log::debug("TegBot Profile: {$name}", $stats);
        
        return $result;
    }
}

// Использование
$profiler = app(BotProfiler::class);

$profiler->profile('command_execution', function () use ($command, $args) {
    return $this->executeCommand($command, $args);
});
```

## Команды мониторинга

### Все команды мониторинга

```bash
# Проверка здоровья
php artisan teg:health

# Подробная диагностика
php artisan teg:diagnose

# Статистика
php artisan teg:stats

# Последние ошибки
php artisan teg:errors --last=24h

# Очистка логов
php artisan teg:logs:cleanup --days=30

# Экспорт метрик
php artisan teg:metrics:export --format=json

# Тест производительности
php artisan teg:benchmark

# Проверка webhook
php artisan teg:webhook:test
```

## Интеграция с внешними системами

### Grafana Dashboard

```json
{
  "dashboard": {
    "title": "TegBot Monitoring",
    "panels": [
      {
        "title": "Messages per minute",
        "type": "graph",
        "targets": [
          {
            "expr": "rate(tegbot_messages_total[1m])",
            "legendFormat": "Messages/min"
          }
        ]
      },
      {
        "title": "Response Time",
        "type": "graph", 
        "targets": [
          {
            "expr": "histogram_quantile(0.95, tegbot_response_time_histogram)",
            "legendFormat": "95th percentile"
          }
        ]
      }
    ]
  }
}
```

### Prometheus метрики

```php
// Экспорт метрик для Prometheus
Route::get('/metrics', function () {
    $metrics = app(BotMetrics::class)->collectMetrics();
    
    $prometheus = '';
    $prometheus .= "# HELP tegbot_messages_total Total messages processed\n";
    $prometheus .= "# TYPE tegbot_messages_total counter\n";
    $prometheus .= "tegbot_messages_total {$metrics['messages']['last_24h']}\n\n";
    
    $prometheus .= "# HELP tegbot_active_users Active users in last 24h\n";
    $prometheus .= "# TYPE tegbot_active_users gauge\n";
    $prometheus .= "tegbot_active_users {$metrics['users']['active_24h']}\n\n";
    
    return response($prometheus)->header('Content-Type', 'text/plain');
});
```

---

📊 **Мониторинг TegBot** - полный контроль над состоянием бота! 