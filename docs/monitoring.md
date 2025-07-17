# 📊 Мониторинг TegBot v2.0

## Обзор системы мониторинга

TegBot v2.0 включает комплексную систему мониторинга для мультиботных экосистем:

- 🎯 **Multi-Bot Monitoring**: Централизованный мониторинг всех ботов
- 📈 **Real-Time Dashboards**: Интерактивные панели в реальном времени
- 🏥 **Health Monitoring**: Детальная проверка здоровья каждого бота
- 📊 **Performance Analytics**: Глубокая аналитика производительности
- 🚨 **Smart Alerting**: Интеллектуальная система оповещений
- 📱 **Mobile-Ready**: Адаптивные интерфейсы для мобильных устройств

> ⚠️ **Важно**: v2.0 полностью переработал систему мониторинга для работы с мультиботными экосистемами.

## 🏥 Health Monitoring

### Комплексная проверка здоровья

```bash
# Проверка всех ботов
php artisan teg:health

# Проверка конкретного бота
php artisan teg:health shop_bot

# Детальная проверка с метриками
php artisan teg:health --detailed --metrics

# Проверка с тестированием API
php artisan teg:health --test-api --timeout=30

# Непрерывный мониторинг
php artisan teg:health --watch --interval=30s
```

**Пример вывода детальной проверки:**
```
🏥 TegBot v2.0 Health Check Report

📊 Общая статистика:
   • Всего ботов: 8
   • Здоровых: 7 ✅
   • С предупреждениями: 1 ⚠️
   • Критических ошибок: 0 ❌
   • Общий статус: HEALTHY

🤖 Статус ботов:
   ✅ shop_bot
      • API: Доступен (45ms)
      • Database: Подключена
      • Webhook: Активен
      • Queue: 3 задач в очереди
      • Memory: 28MB/512MB (5%)
      • Uptime: 7d 12h 45m

   ⚠️  support_bot
      • API: Доступен (120ms) - медленный ответ
      • Database: Подключена
      • Webhook: Активен
      • Queue: 25 задач в очереди - высокая нагрузка
      • Memory: 156MB/512MB (30%)
      • Uptime: 2d 8h 15m

   ✅ analytics_bot
      • API: Доступен (22ms)
      • Database: Подключена
      • Webhook: Активен
      • Queue: 0 задач в очереди
      • Memory: 45MB/512MB (9%)
      • Uptime: 10d 3h 22m

📈 Производительность (24ч):
   • Обработано сообщений: 45,283
   • Средний ответ: 78ms
   • Успешных операций: 99.8%
   • Ошибок: 0.2% (89 ошибок)
   • Пиковая нагрузка: 1,250 сообщений/час

🔧 Рекомендации:
   • support_bot: Оптимизировать обработку очереди
   • Все боты: Обновить до последней версии
   • Настроить автоскейлинг для пиковых нагрузок
```

### Автоматизированные health checks

```php
<?php
// app/TegBot/Health/HealthChecker.php
namespace App\TegBot\Health;

use App\Models\Bot;
use GuzzleHttp\Client;

class BotHealthChecker
{
    private Client $httpClient;
    private array $healthChecks = [];
    
    public function __construct()
    {
        $this->httpClient = new Client(['timeout' => 10]);
        $this->initializeHealthChecks();
    }
    
    public function checkAllBots(): array
    {
        $results = [];
        $bots = Bot::where('status', 'active')->get();
        
        foreach ($bots as $bot) {
            $results[$bot->name] = $this->checkBot($bot);
        }
        
        return $results;
    }
    
    public function checkBot(Bot $bot): array
    {
        $health = [
            'bot_name' => $bot->name,
            'status' => 'unknown',
            'checks' => [],
            'metrics' => [],
            'timestamp' => now()
        ];
        
        foreach ($this->healthChecks as $checkName => $checker) {
            try {
                $result = $checker->check($bot);
                $health['checks'][$checkName] = $result;
            } catch (\Exception $e) {
                $health['checks'][$checkName] = [
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'duration_ms' => 0
                ];
            }
        }
        
        $health['status'] = $this->calculateOverallStatus($health['checks']);
        $health['metrics'] = $this->collectMetrics($bot);
        
        return $health;
    }
    
    private function initializeHealthChecks(): void
    {
        $this->healthChecks = [
            'api_connectivity' => new ApiConnectivityCheck(),
            'database_connection' => new DatabaseConnectionCheck(),
            'webhook_status' => new WebhookStatusCheck(),
            'queue_health' => new QueueHealthCheck(),
            'memory_usage' => new MemoryUsageCheck(),
            'response_time' => new ResponseTimeCheck(),
            'error_rate' => new ErrorRateCheck(),
        ];
    }
    
    private function calculateOverallStatus(array $checks): string
    {
        $failedChecks = array_filter($checks, fn($check) => $check['status'] === 'failed');
        $warningChecks = array_filter($checks, fn($check) => $check['status'] === 'warning');
        
        if (!empty($failedChecks)) {
            return 'failed';
        } elseif (!empty($warningChecks)) {
            return 'warning';
        } else {
            return 'healthy';
        }
    }
}
```

### Health Check Components

```php
<?php
// Проверка API connectivity
class ApiConnectivityCheck implements HealthCheckInterface
{
    public function check(Bot $bot): array
    {
        $startTime = microtime(true);
        
        try {
            $response = $this->httpClient->get("https://api.telegram.org/bot{$bot->token}/getMe");
            $endTime = microtime(true);
            
            $responseTime = round(($endTime - $startTime) * 1000, 2);
            
            if ($response->getStatusCode() === 200) {
                $status = $responseTime > 1000 ? 'warning' : 'healthy';
                return [
                    'status' => $status,
                    'response_time_ms' => $responseTime,
                    'message' => $status === 'warning' ? 'Slow API response' : 'API accessible'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'duration_ms' => 0
            ];
        }
    }
}

// Проверка webhook статуса
class WebhookStatusCheck implements HealthCheckInterface
{
    public function check(Bot $bot): array
    {
        try {
            $response = $this->httpClient->get("https://api.telegram.org/bot{$bot->token}/getWebhookInfo");
            $webhookInfo = json_decode($response->getBody(), true);
            
            $expectedUrl = config('app.url') . "/webhook/{$bot->name}";
            $actualUrl = $webhookInfo['result']['url'] ?? '';
            
            if ($actualUrl === $expectedUrl) {
                return [
                    'status' => 'healthy',
                    'url' => $actualUrl,
                    'last_error_date' => $webhookInfo['result']['last_error_date'] ?? null
                ];
            } else {
                return [
                    'status' => 'failed',
                    'expected_url' => $expectedUrl,
                    'actual_url' => $actualUrl,
                    'message' => 'Webhook URL mismatch'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }
}

// Проверка состояния очереди
class QueueHealthCheck implements HealthCheckInterface
{
    public function check(Bot $bot): array
    {
        $queueSize = \Queue::size();
        $failedJobs = \DB::table('failed_jobs')->count();
        
        $status = 'healthy';
        $message = 'Queue is healthy';
        
        if ($queueSize > 1000) {
            $status = 'warning';
            $message = 'High queue size';
        } elseif ($queueSize > 5000) {
            $status = 'failed';
            $message = 'Critical queue size';
        }
        
        if ($failedJobs > 10) {
            $status = 'warning';
            $message = 'Many failed jobs';
        }
        
        return [
            'status' => $status,
            'queue_size' => $queueSize,
            'failed_jobs' => $failedJobs,
            'message' => $message
        ];
    }
}
```

## 📈 Performance Monitoring

### Метрики производительности

```bash
# Общая статистика производительности
php artisan teg:performance

# Статистика по конкретному боту
php artisan teg:performance shop_bot

# Детальная аналитика с графиками
php artisan teg:performance --detailed --graphs

# Сравнение производительности ботов
php artisan teg:performance --compare --period=7d

# Экспорт метрик
php artisan teg:performance --export=json --period=30d
```

**Пример отчета производительности:**
```
📈 Performance Report - TegBot v2.0

⏱️  Response Time Analysis:
   • Средний ответ: 127ms
   • Медианный ответ: 89ms
   • 95-й percentile: 340ms
   • 99-й percentile: 890ms
   • Самый медленный: 2.1s (support_bot/ticket_create)

📊 Throughput Analysis:
   • Сообщений в секунду: 24.5 avg, 156 peak
   • Команд в минуту: 847 avg, 2,340 peak
   • Callback'ов в минуту: 1,234 avg, 4,567 peak

🤖 Bot Performance Comparison:
   ┌─────────────────┬──────────────┬──────────────┬──────────────┐
   │ Bot             │ Avg Response │ Messages/min │ Success Rate │
   ├─────────────────┼──────────────┼──────────────┼──────────────┤
   │ shop_bot        │ 78ms         │ 445          │ 99.9%        │
   │ support_bot     │ 234ms        │ 156          │ 98.7%        │
   │ analytics_bot   │ 45ms         │ 67           │ 100%         │
   │ notifications   │ 23ms         │ 89           │ 99.8%        │
   └─────────────────┴──────────────┴──────────────┴──────────────┘

🔥 Hotspots (slowest operations):
   1. support_bot: create_ticket (avg: 1.2s)
   2. shop_bot: process_payment (avg: 890ms)
   3. analytics_bot: generate_report (avg: 650ms)

💾 Memory Usage:
   • shop_bot: 89MB avg, 156MB peak
   • support_bot: 134MB avg, 289MB peak
   • analytics_bot: 67MB avg, 98MB peak

🔄 Database Query Analysis:
   • Queries per request: 3.4 avg, 23 max
   • Slow queries (>100ms): 12 (0.08%)
   • Most expensive: user_activity_stats (456ms)
```

### Real-time Performance Tracking

```php
<?php
// app/TegBot/Monitoring/PerformanceTracker.php
namespace App\TegBot\Monitoring;

use App\Models\PerformanceMetric;
use Illuminate\Support\Facades\Cache;

class PerformanceTracker
{
    private array $metrics = [];
    
    public function startTracking(string $botName, string $operation): string
    {
        $trackingId = uniqid('track_');
        
        $this->metrics[$trackingId] = [
            'bot_name' => $botName,
            'operation' => $operation,
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true),
            'start_queries' => $this->getQueryCount(),
        ];
        
        return $trackingId;
    }
    
    public function endTracking(string $trackingId): void
    {
        if (!isset($this->metrics[$trackingId])) {
            return;
        }
        
        $metric = $this->metrics[$trackingId];
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $endQueries = $this->getQueryCount();
        
        $performanceData = [
            'bot_name' => $metric['bot_name'],
            'operation' => $metric['operation'],
            'duration_ms' => round(($endTime - $metric['start_time']) * 1000, 2),
            'memory_used_mb' => round(($endMemory - $metric['start_memory']) / 1024 / 1024, 2),
            'queries_count' => $endQueries - $metric['start_queries'],
            'timestamp' => now(),
        ];
        
        // Сохраняем метрику
        PerformanceMetric::create($performanceData);
        
        // Обновляем real-time кэш
        $this->updateRealTimeMetrics($performanceData);
        
        // Проверяем на аномалии
        $this->checkPerformanceAnomalies($performanceData);
        
        unset($this->metrics[$trackingId]);
    }
    
    private function updateRealTimeMetrics(array $data): void
    {
        $key = "realtime_metrics:{$data['bot_name']}";
        $currentMetrics = Cache::get($key, []);
        
        $currentMetrics[] = [
            'operation' => $data['operation'],
            'duration_ms' => $data['duration_ms'],
            'timestamp' => $data['timestamp']
        ];
        
        // Храним только последние 100 операций
        if (count($currentMetrics) > 100) {
            $currentMetrics = array_slice($currentMetrics, -100);
        }
        
        Cache::put($key, $currentMetrics, 300); // 5 минут
    }
    
    private function checkPerformanceAnomalies(array $data): void
    {
        // Проверяем на медленные операции
        if ($data['duration_ms'] > 5000) { // 5 секунд
            $this->sendPerformanceAlert('slow_operation', $data);
        }
        
        // Проверяем на высокое потребление памяти
        if ($data['memory_used_mb'] > 100) { // 100MB
            $this->sendPerformanceAlert('high_memory_usage', $data);
        }
        
        // Проверяем на много запросов к БД
        if ($data['queries_count'] > 50) {
            $this->sendPerformanceAlert('high_query_count', $data);
        }
    }
    
    private function sendPerformanceAlert(string $type, array $data): void
    {
        event(new PerformanceAnomalyDetected($type, $data));
    }
}
```

### Automated Performance Analysis

```php
<?php
// app/TegBot/Monitoring/PerformanceAnalyzer.php
namespace App\TegBot\Monitoring;

class PerformanceAnalyzer
{
    public function analyzeDaily(): array
    {
        $yesterday = now()->subDay();
        
        return [
            'summary' => $this->getDailySummary($yesterday),
            'bottlenecks' => $this->identifyBottlenecks($yesterday),
            'trends' => $this->analyzeTrends($yesterday),
            'recommendations' => $this->generateRecommendations($yesterday)
        ];
    }
    
    private function identifyBottlenecks(Carbon $date): array
    {
        $slowOperations = PerformanceMetric::whereDate('created_at', $date)
            ->select('bot_name', 'operation')
            ->selectRaw('AVG(duration_ms) as avg_duration')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('bot_name', 'operation')
            ->having('avg_duration', '>', 1000) // Более 1 секунды
            ->orderBy('avg_duration', 'desc')
            ->limit(10)
            ->get();
            
        $memoryHogs = PerformanceMetric::whereDate('created_at', $date)
            ->select('bot_name', 'operation')
            ->selectRaw('AVG(memory_used_mb) as avg_memory')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('bot_name', 'operation')
            ->having('avg_memory', '>', 50) // Более 50MB
            ->orderBy('avg_memory', 'desc')
            ->limit(10)
            ->get();
            
        return [
            'slow_operations' => $slowOperations,
            'memory_intensive' => $memoryHogs
        ];
    }
    
    private function generateRecommendations(Carbon $date): array
    {
        $recommendations = [];
        
        // Анализируем медленные операции
        $slowOps = $this->getSlowOperations($date);
        foreach ($slowOps as $op) {
            if ($op->avg_duration > 2000) {
                $recommendations[] = [
                    'type' => 'optimization',
                    'priority' => 'high',
                    'bot' => $op->bot_name,
                    'operation' => $op->operation,
                    'message' => "Оптимизировать операцию {$op->operation} в {$op->bot_name} (avg: {$op->avg_duration}ms)"
                ];
            }
        }
        
        // Анализируем использование памяти
        $memoryUsage = $this->getMemoryUsage($date);
        foreach ($memoryUsage as $usage) {
            if ($usage->avg_memory > 200) {
                $recommendations[] = [
                    'type' => 'memory',
                    'priority' => 'medium',
                    'bot' => $usage->bot_name,
                    'message' => "Проверить утечки памяти в {$usage->bot_name} (avg: {$usage->avg_memory}MB)"
                ];
            }
        }
        
        return $recommendations;
    }
}
```

## 🚨 Alerting System

### Настройка системы оповещений

```bash
# Настройка алертов
php artisan teg:alerts setup

# Добавление правил алертов
php artisan teg:alerts add response_time --threshold=1000ms --severity=warning
php artisan teg:alerts add error_rate --threshold=5% --severity=critical
php artisan teg:alerts add queue_size --threshold=500 --severity=warning
php artisan teg:alerts add memory_usage --threshold=80% --severity=critical

# Настройка каналов уведомлений
php artisan teg:alerts channel telegram --bot=admin_bot --chat=-123456789
php artisan teg:alerts channel email --to=admin@company.com
php artisan teg:alerts channel slack --webhook=https://hooks.slack.com/...
php artisan teg:alerts channel discord --webhook=https://discord.com/api/webhooks/...

# Тестирование алертов
php artisan teg:alerts test
php artisan teg:alerts test --channel=telegram
```

### Smart Alerting Engine

```php
<?php
// app/TegBot/Monitoring/AlertEngine.php
namespace App\TegBot\Monitoring;

use App\Models\AlertRule;
use App\Models\AlertHistory;

class AlertEngine
{
    private array $channels = [];
    
    public function __construct()
    {
        $this->initializeChannels();
    }
    
    public function checkAlerts(): void
    {
        $rules = AlertRule::where('enabled', true)->get();
        
        foreach ($rules as $rule) {
            $currentValue = $this->getCurrentValue($rule);
            
            if ($this->shouldTriggerAlert($rule, $currentValue)) {
                $this->triggerAlert($rule, $currentValue);
            } elseif ($this->shouldResolveAlert($rule, $currentValue)) {
                $this->resolveAlert($rule, $currentValue);
            }
        }
    }
    
    private function shouldTriggerAlert(AlertRule $rule, $currentValue): bool
    {
        // Проверяем, не в состоянии ли уже алерт
        if ($rule->status === 'triggered') {
            return false;
        }
        
        switch ($rule->operator) {
            case 'greater_than':
                return $currentValue > $rule->threshold;
            case 'less_than':
                return $currentValue < $rule->threshold;
            case 'equals':
                return $currentValue == $rule->threshold;
            case 'not_equals':
                return $currentValue != $rule->threshold;
            default:
                return false;
        }
    }
    
    private function triggerAlert(AlertRule $rule, $currentValue): void
    {
        $alert = [
            'rule_id' => $rule->id,
            'rule_name' => $rule->name,
            'severity' => $rule->severity,
            'current_value' => $currentValue,
            'threshold' => $rule->threshold,
            'bot_name' => $rule->bot_name,
            'triggered_at' => now(),
            'message' => $this->generateAlertMessage($rule, $currentValue)
        ];
        
        // Сохраняем в историю
        AlertHistory::create($alert);
        
        // Обновляем статус правила
        $rule->update(['status' => 'triggered']);
        
        // Отправляем уведомления
        $this->sendAlert($alert);
        
        // Проверяем на cascade алерты
        $this->checkCascadeAlerts($rule, $currentValue);
    }
    
    private function generateAlertMessage(AlertRule $rule, $currentValue): string
    {
        $templates = [
            'response_time' => "🐌 Медленный ответ в {bot_name}: {current_value}ms > {threshold}ms",
            'error_rate' => "🚨 Высокий уровень ошибок в {bot_name}: {current_value}% > {threshold}%",
            'memory_usage' => "💾 Высокое потребление памяти в {bot_name}: {current_value}MB > {threshold}MB",
            'queue_size' => "📋 Большая очередь в {bot_name}: {current_value} > {threshold}",
            'bot_down' => "💀 Бот {bot_name} недоступен",
            'webhook_failed' => "🔗 Webhook {bot_name} не работает"
        ];
        
        $template = $templates[$rule->metric] ?? "⚠️ Алерт {rule_name}: {current_value} {operator} {threshold}";
        
        return strtr($template, [
            '{bot_name}' => $rule->bot_name,
            '{rule_name}' => $rule->name,
            '{current_value}' => $currentValue,
            '{threshold}' => $rule->threshold,
            '{operator}' => $rule->operator
        ]);
    }
    
    private function sendAlert(array $alert): void
    {
        foreach ($this->channels as $channel) {
            if ($this->shouldSendToChannel($channel, $alert)) {
                $channel->send($alert);
            }
        }
    }
    
    private function shouldSendToChannel($channel, array $alert): bool
    {
        // Проверяем настройки канала
        if (isset($channel->severities) && !in_array($alert['severity'], $channel->severities)) {
            return false;
        }
        
        if (isset($channel->bots) && !in_array($alert['bot_name'], $channel->bots)) {
            return false;
        }
        
        // Проверяем quiet hours
        if ($channel->hasQuietHours() && $channel->isQuietTime()) {
            return $alert['severity'] === 'critical';
        }
        
        return true;
    }
}
```

### Alert Channels

```php
<?php
// Telegram канал для алертов
class TelegramAlertChannel implements AlertChannelInterface
{
    private string $botToken;
    private string $chatId;
    
    public function send(array $alert): void
    {
        $message = $this->formatMessage($alert);
        
        $this->httpClient->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'json' => [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'reply_markup' => $this->getInlineKeyboard($alert)
            ]
        ]);
    }
    
    private function formatMessage(array $alert): string
    {
        $emoji = $this->getSeverityEmoji($alert['severity']);
        
        $message = "{$emoji} **Алерт TegBot**\n\n";
        $message .= "🤖 **Бот:** {$alert['bot_name']}\n";
        $message .= "📊 **Метрика:** {$alert['rule_name']}\n";
        $message .= "📈 **Значение:** {$alert['current_value']}\n";
        $message .= "⚠️ **Порог:** {$alert['threshold']}\n";
        $message .= "🕐 **Время:** " . $alert['triggered_at']->format('d.m.Y H:i:s');
        
        return $message;
    }
    
    private function getInlineKeyboard(array $alert): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '📊 Dashboard', 'url' => config('app.url') . '/monitoring'],
                    ['text' => '🔍 Details', 'callback_data' => "alert_details_{$alert['rule_id']}"]
                ],
                [
                    ['text' => '✅ Acknowledge', 'callback_data' => "acknowledge_{$alert['rule_id']}"],
                    ['text' => '🔇 Silence 1h', 'callback_data' => "silence_{$alert['rule_id']}_1h"]
                ]
            ]
        ];
    }
}

// Email канал для алертов
class EmailAlertChannel implements AlertChannelInterface
{
    public function send(array $alert): void
    {
        Mail::to($this->recipients)->send(new AlertNotification($alert));
    }
}

// Slack канал для алертов
class SlackAlertChannel implements AlertChannelInterface
{
    public function send(array $alert): void
    {
        $payload = [
            'text' => $alert['message'],
            'attachments' => [
                [
                    'color' => $this->getSeverityColor($alert['severity']),
                    'fields' => [
                        ['title' => 'Bot', 'value' => $alert['bot_name'], 'short' => true],
                        ['title' => 'Current Value', 'value' => $alert['current_value'], 'short' => true],
                        ['title' => 'Threshold', 'value' => $alert['threshold'], 'short' => true],
                        ['title' => 'Time', 'value' => $alert['triggered_at']->format('Y-m-d H:i:s'), 'short' => true],
                    ]
                ]
            ]
        ];
        
        $this->httpClient->post($this->webhookUrl, ['json' => $payload]);
    }
}
```

## 📱 Real-time Dashboard

### Web Dashboard

```php
<?php
// app/Http/Controllers/MonitoringController.php
namespace App\Http\Controllers;

use App\TegBot\Monitoring\DashboardService;

class MonitoringController extends Controller
{
    public function dashboard()
    {
        $dashboardData = app(DashboardService::class)->getDashboardData();
        
        return view('monitoring.dashboard', $dashboardData);
    }
    
    public function realTimeMetrics()
    {
        return response()->json([
            'bots' => $this->getBotStatuses(),
            'metrics' => $this->getRealTimeMetrics(),
            'alerts' => $this->getActiveAlerts(),
            'timestamp' => now()
        ]);
    }
    
    public function botDetails(string $botName)
    {
        $bot = Bot::where('name', $botName)->firstOrFail();
        
        return response()->json([
            'health' => app(BotHealthChecker::class)->checkBot($bot),
            'metrics' => $this->getBotMetrics($bot),
            'recent_activity' => $this->getRecentActivity($bot)
        ]);
    }
}
```

### WebSocket для real-time обновлений

```javascript
// resources/js/monitoring-dashboard.js
class MonitoringDashboard {
    constructor() {
        this.ws = null;
        this.charts = {};
        this.initializeWebSocket();
        this.initializeCharts();
    }
    
    initializeWebSocket() {
        this.ws = new WebSocket(`ws://localhost:6001/monitoring`);
        
        this.ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.updateDashboard(data);
        };
        
        this.ws.onclose = () => {
            // Переподключение через 5 секунд
            setTimeout(() => this.initializeWebSocket(), 5000);
        };
    }
    
    updateDashboard(data) {
        // Обновляем статусы ботов
        this.updateBotStatuses(data.bots);
        
        // Обновляем метрики
        this.updateMetrics(data.metrics);
        
        // Обновляем алерты
        this.updateAlerts(data.alerts);
        
        // Обновляем графики
        this.updateCharts(data.metrics);
    }
    
    updateBotStatuses(bots) {
        bots.forEach(bot => {
            const statusElement = document.getElementById(`bot-status-${bot.name}`);
            if (statusElement) {
                statusElement.className = `bot-status ${bot.status}`;
                statusElement.textContent = bot.status.toUpperCase();
            }
            
            const responseTimeElement = document.getElementById(`bot-response-${bot.name}`);
            if (responseTimeElement) {
                responseTimeElement.textContent = `${bot.response_time}ms`;
            }
        });
    }
    
    updateCharts(metrics) {
        // Обновляем график времени ответа
        if (this.charts.responseTime) {
            this.charts.responseTime.data.datasets[0].data.push({
                x: new Date(),
                y: metrics.avg_response_time
            });
            
            // Оставляем только последние 50 точек
            if (this.charts.responseTime.data.datasets[0].data.length > 50) {
                this.charts.responseTime.data.datasets[0].data.shift();
            }
            
            this.charts.responseTime.update('none');
        }
        
        // Обновляем график пропускной способности
        if (this.charts.throughput) {
            this.charts.throughput.data.datasets[0].data.push({
                x: new Date(),
                y: metrics.messages_per_minute
            });
            
            if (this.charts.throughput.data.datasets[0].data.length > 50) {
                this.charts.throughput.data.datasets[0].data.shift();
            }
            
            this.charts.throughput.update('none');
        }
    }
}

// Инициализация dashboard при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    new MonitoringDashboard();
});
```

## 📊 Analytics & Reporting

### Автоматические отчеты

```bash
# Генерация отчетов
php artisan teg:reports generate daily
php artisan teg:reports generate weekly
php artisan teg:reports generate monthly

# Отправка отчетов
php artisan teg:reports send daily --email=admin@company.com
php artisan teg:reports send weekly --telegram=@admin_channel

# Кастомные отчеты
php artisan teg:reports custom --period="2024-01-01:2024-01-31" --bots=shop,support
php artisan teg:reports performance --include-recommendations
```

### Report Generator

```php
<?php
// app/TegBot/Monitoring/ReportGenerator.php
namespace App\TegBot\Monitoring;

class ReportGenerator
{
    public function generateDailyReport(Carbon $date): array
    {
        return [
            'period' => $date->format('Y-m-d'),
            'summary' => $this->getDailySummary($date),
            'bot_performance' => $this->getBotPerformance($date),
            'top_operations' => $this->getTopOperations($date),
            'error_analysis' => $this->getErrorAnalysis($date),
            'recommendations' => $this->getRecommendations($date),
            'charts' => $this->generateCharts($date)
        ];
    }
    
    private function getDailySummary(Carbon $date): array
    {
        $metrics = PerformanceMetric::whereDate('created_at', $date);
        
        return [
            'total_requests' => $metrics->count(),
            'avg_response_time' => round($metrics->avg('duration_ms'), 2),
            'total_errors' => $metrics->whereNotNull('error')->count(),
            'success_rate' => round((1 - $metrics->whereNotNull('error')->count() / $metrics->count()) * 100, 2),
            'peak_hour' => $this->getPeakHour($date),
            'slowest_operation' => $this->getSlowestOperation($date)
        ];
    }
    
    private function generateCharts(Carbon $date): array
    {
        return [
            'hourly_throughput' => $this->getHourlyThroughput($date),
            'response_time_distribution' => $this->getResponseTimeDistribution($date),
            'bot_comparison' => $this->getBotComparison($date),
            'error_breakdown' => $this->getErrorBreakdown($date)
        ];
    }
}
```

## 🔧 CLI Monitoring Tools

### Интерактивные команды мониторинга

```bash
# Интерактивный dashboard в терминале
php artisan teg:monitor --interactive

# Real-time логи
php artisan teg:logs --follow --bot=shop_bot
php artisan teg:logs --follow --level=error

# Top операций в реальном времени
php artisan teg:top --operations --refresh=5s

# Мониторинг конкретного бота
php artisan teg:watch shop_bot --metrics=response_time,memory,queue

# Network мониторинг
php artisan teg:network --test-webhooks --test-api
```

## 📚 Best Practices

### 🎯 Настройка мониторинга
1. **Определите ключевые метрики** для каждого бота
2. **Настройте разумные пороги** для алертов
3. **Используйте разные каналы** для разных типов алертов
4. **Регулярно пересматривайте** настройки мониторинга

### 🚨 Управление алертами
1. **Избегайте спама** - настройте объединение похожих алертов
2. **Используйте escalation** для критичных алертов
3. **Настройте quiet hours** для некритичных уведомлений
4. **Документируйте runbook'и** для каждого типа алерта

### 📊 Анализ производительности
1. **Мониторьте тренды**, а не только текущие значения
2. **Корреляция метрик** поможет найти первопричины
3. **Автоматизируйте генерацию отчетов**
4. **Используйте профилирование** для оптимизации

### 🔧 Операционная готовность
1. **Проверяйте алерты** регулярно
2. **Тестируйте recovery процедуры**
3. **Ведите post-mortem** после инцидентов
4. **Обучайте команду** работе с monitoring tools

---

📊 **TegBot v2.0 Monitoring** - Полный контроль над вашей мультиботной экосистемой! 