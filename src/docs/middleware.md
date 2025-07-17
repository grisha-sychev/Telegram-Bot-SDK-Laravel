# 🔄 Middleware система TegBot

## Обзор

Middleware в TegBot позволяет обрабатывать сообщения до их передачи основной логике бота:

- ⚡ **Pipeline обработка**: Последовательное выполнение middleware
- 🛡️ **Безопасность**: Встроенные фильтры для защиты
- 🎯 **Фильтрация**: Селективная обработка по типам сообщений
- 📊 **Логирование**: Автоматическое отслеживание активности
- 🔧 **Кастомизация**: Возможность создания собственных middleware

## Типы middleware

### Глобальные middleware

Выполняются для всех сообщений:

```php
public function main(): void
{
    // Применяем глобальные middleware
    $this->globalMiddleware([
        'spam_protection',    // защита от спама
        'activity_logging',   // логирование активности  
        'rate_limiting',      // ограничение частоты
        'user_validation',    // валидация пользователей
    ]);
    
    // Остальная логика бота
}
```

### Middleware для команд

Выполняются только для конкретных команд:

```php
$this->registerCommand('admin', $callback, [
    'middleware' => [
        'check_admin_rights',
        'validate_admin_command',
        function ($bot, $parsed) {
            // Кастомная проверка
            return $bot->isWorkingHours();
        }
    ],
]);
```

## Встроенные middleware

### spam_protection

Защита от спама и флуда:

```php
// Автоматическая настройка
$this->globalMiddleware(['spam_protection']);

// Кастомные настройки
$this->middleware('spam_protection', [
    'max_messages_per_minute' => 20,
    'ban_duration_minutes' => 60,
    'whitelist_admins' => true,
]);
```

**Функции:**
- Ограничение количества сообщений в минуту
- Автоматическая блокировка нарушителей
- Исключения для администраторов
- Логирование попыток спама

### activity_logging

Логирование активности пользователей:

```php
$this->globalMiddleware(['activity_logging']);
```

**Что логируется:**
- Все входящие сообщения
- Выполненные команды
- Медиа файлы
- Время и тип чата
- IP-адрес (если доступен)

### rate_limiting

Ограничение частоты запросов:

```php
$this->middleware('rate_limiting', [
    'global_limit' => 100,      // глобальный лимит
    'per_user_limit' => 20,     // лимит на пользователя
    'per_chat_limit' => 50,     // лимит на чат
    'window_minutes' => 1,      // окно времени
]);
```

### user_validation

Валидация пользователей:

```php
$this->middleware('user_validation', [
    'require_username' => false,
    'min_account_age_days' => 0,
    'blocked_users' => [],
    'allowed_users' => [], // если пустой, то все разрешены
]);
```

## Создание кастомных middleware

### Функция middleware

```php
private function myCustomMiddleware($bot, $parsed): bool
{
    // $bot - экземпляр бота
    // $parsed - разобранное сообщение
    
    // Ваша логика
    if (!$this->someCondition()) {
        $bot->sendSelf('❌ Условие не выполнено');
        return false; // Блокируем дальнейшую обработку
    }
    
    return true; // Разрешаем продолжить
}

// Регистрация
$this->middleware([$this, 'myCustomMiddleware']);
```

### Класс middleware

```php
class BusinessHoursMiddleware
{
    public function handle($bot, $parsed, $next)
    {
        $hour = now()->hour;
        
        if ($hour < 9 || $hour > 18) {
            $bot->sendSelf('⏰ Бот работает только в рабочее время (9:00-18:00)');
            return false;
        }
        
        return $next($bot, $parsed);
    }
}

// Использование
$this->middleware(new BusinessHoursMiddleware());
```

### Middleware с параметрами

```php
class RoleMiddleware
{
    private array $requiredRoles;
    
    public function __construct(array $roles)
    {
        $this->requiredRoles = $roles;
    }
    
    public function handle($bot, $parsed, $next)
    {
        $userId = $bot->getUserId;
        $userRoles = $this->getUserRoles($userId);
        
        $hasRequiredRole = false;
        foreach ($this->requiredRoles as $role) {
            if (in_array($role, $userRoles)) {
                $hasRequiredRole = true;
                break;
            }
        }
        
        if (!$hasRequiredRole) {
            $rolesStr = implode(', ', $this->requiredRoles);
            $bot->sendSelf("🚫 Требуется одна из ролей: {$rolesStr}");
            return false;
        }
        
        return $next($bot, $parsed);
    }
    
    private function getUserRoles(int $userId): array
    {
        // Логика получения ролей пользователя
        return User::find($userId)?->roles ?? [];
    }
}

// Использование
$this->registerCommand('moderate', $callback, [
    'middleware' => [
        new RoleMiddleware(['moderator', 'admin']),
    ],
]);
```

## Pipeline обработка

### Последовательность выполнения

```php
public function main(): void
{
    // 1. Глобальные middleware выполняются первыми
    $this->globalMiddleware([
        'security_check',      // проверка безопасности
        'user_authentication', // аутентификация
        'rate_limiting',       // ограничения
        'logging',            // логирование
    ]);
    
    // 2. Затем middleware команд (если это команда)
    $this->registerCommand('sensitive', $callback, [
        'middleware' => [
            'additional_auth',  // дополнительная аутентификация
            'audit_logging',    // аудит логирование
        ],
    ]);
}
```

### Остановка pipeline

```php
private function securityCheckMiddleware($bot, $parsed): bool
{
    // Проверяем на подозрительную активность
    if ($this->detectSuspiciousActivity($bot->getUserId)) {
        // Блокируем пользователя
        $this->blockUser($bot->getUserId);
        
        // Уведомляем админов
        $this->notifyAdmins("🚨 Заблокирован пользователь: {$bot->getUserId}");
        
        // ОСТАНАВЛИВАЕМ обработку
        return false;
    }
    
    return true;
}
```

## Условные middleware

### Middleware по типу сообщения

```php
private function mediaOnlyMiddleware($bot, $parsed): bool
{
    $messageType = $bot->getMessageType();
    
    if (!in_array($messageType, ['photo', 'video', 'document'])) {
        $bot->sendSelf('📎 Отправьте медиа файл');
        return false;
    }
    
    return true;
}

// Применение только к определенным командам
$this->registerCommand('upload', $callback, [
    'middleware' => [[$this, 'mediaOnlyMiddleware']],
]);
```

### Middleware по времени

```php
private function weekdaysOnlyMiddleware($bot, $parsed): bool
{
    $dayOfWeek = now()->dayOfWeek;
    
    // 1 = понедельник, 7 = воскресенье
    if ($dayOfWeek < 1 || $dayOfWeek > 5) {
        $bot->sendSelf('📅 Команда доступна только в рабочие дни');
        return false;
    }
    
    return true;
}
```

### Middleware по размеру чата

```php
private function smallGroupsOnlyMiddleware($bot, $parsed): bool
{
    if ($bot->getChatType() !== 'group') {
        return true; // Пропускаем приватные чаты
    }
    
    $chatMembersCount = $bot->getChatMembersCount();
    
    if ($chatMembersCount > 100) {
        $bot->sendSelf('👥 Команда доступна только в небольших группах (до 100 участников)');
        return false;
    }
    
    return true;
}
```

## Middleware для безопасности

### Валидация входных данных

```php
private function inputValidationMiddleware($bot, $parsed): bool
{
    $text = $bot->getMessageText();
    
    if (!$text) {
        return true; // Пропускаем не-текстовые сообщения
    }
    
    // Проверка на SQL инъекции
    $sqlPatterns = [
        '/union\s+select/i',
        '/drop\s+table/i',
        '/delete\s+from/i',
        '/insert\s+into/i',
    ];
    
    foreach ($sqlPatterns as $pattern) {
        if (preg_match($pattern, $text)) {
            $bot->sendSelf('🚫 Обнаружена попытка SQL инъекции');
            $this->logSecurityIncident('sql_injection_attempt', [
                'user_id' => $bot->getUserId,
                'text' => $text,
            ]);
            return false;
        }
    }
    
    // Проверка на XSS
    if (strip_tags($text) !== $text) {
        $bot->sendSelf('🚫 HTML теги не разрешены');
        return false;
    }
    
    // Проверка длины сообщения
    if (strlen($text) > 4000) {
        $bot->sendSelf('📝 Сообщение слишком длинное (максимум 4000 символов)');
        return false;
    }
    
    return true;
}
```

### Защита от ботов

```php
private function antiBotMiddleware($bot, $parsed): bool
{
    $userId = $bot->getUserId;
    
    // Проверяем паттерны поведения ботов
    $patterns = $this->checkBotPatterns($userId);
    
    if ($patterns['is_likely_bot']) {
        $bot->sendSelf('🤖 Обнаружено автоматизированное поведение');
        
        // Отправляем CAPTCHA
        $this->sendCaptcha($bot);
        
        return false;
    }
    
    return true;
}

private function checkBotPatterns(int $userId): array
{
    $recentMessages = Cache::get("user_messages:{$userId}", []);
    
    // Слишком быстрые сообщения
    $tooFast = $this->checkMessageSpeed($recentMessages);
    
    // Одинаковые сообщения
    $repeating = $this->checkRepeatingMessages($recentMessages);
    
    // Подозрительные интервалы
    $suspiciousIntervals = $this->checkIntervals($recentMessages);
    
    return [
        'is_likely_bot' => $tooFast || $repeating || $suspiciousIntervals,
        'fast_messages' => $tooFast,
        'repeating' => $repeating,
        'suspicious_intervals' => $suspiciousIntervals,
    ];
}
```

## Middleware для логирования

### Подробное логирование

```php
private function detailedLoggingMiddleware($bot, $parsed): bool
{
    $logData = [
        'user_id' => $bot->getUserId,
        'chat_id' => $bot->getChatId,
        'chat_type' => $bot->getChatType(),
        'message_type' => $bot->getMessageType(),
        'timestamp' => now()->toISOString(),
        'user_agent' => request()->userAgent(),
        'ip_address' => request()->ip(),
    ];
    
    // Добавляем информацию о сообщении
    if ($bot->hasMessageText()) {
        $logData['text_length'] = strlen($bot->getMessageText());
        $logData['is_command'] = $bot->isMessageCommand();
    }
    
    // Добавляем информацию о медиа
    if ($bot->getMessageType() !== 'text') {
        $logData['media_info'] = $this->getMediaInfo($bot);
    }
    
    // Сохраняем лог
    $this->saveDetailedLog($logData);
    
    return true;
}

private function getMediaInfo($bot): array
{
    $type = $bot->getMessageType();
    
    switch ($type) {
        case 'photo':
            return $bot->getPhotoInfo();
        case 'video':
            return $bot->getVideoInfo();
        case 'document':
            return $bot->getDocumentInfo();
        default:
            return ['type' => $type];
    }
}
```

### Аудит команд

```php
private function commandAuditMiddleware($bot, $parsed): bool
{
    if (!$bot->isMessageCommand()) {
        return true;
    }
    
    $commandText = $bot->getMessageText();
    $parts = explode(' ', ltrim($commandText, '/'));
    $command = $parts[0];
    $args = array_slice($parts, 1);
    
    // Логируем выполнение команды
    $this->auditLog('command_execution', [
        'command' => $command,
        'args' => $args,
        'user_id' => $bot->getUserId,
        'chat_id' => $bot->getChatId,
        'timestamp' => now(),
        'success' => null, // Будет установлено после выполнения
    ]);
    
    return true;
}
```

## Middleware для производительности

### Кэширование

```php
private function cachingMiddleware($bot, $parsed): bool
{
    $cacheKey = $this->generateCacheKey($bot);
    
    // Проверяем кэш
    $cachedResponse = Cache::get($cacheKey);
    
    if ($cachedResponse) {
        $bot->sendSelf($cachedResponse);
        
        // Логируем cache hit
        $this->logActivity('cache_hit', [
            'key' => $cacheKey,
            'user_id' => $bot->getUserId,
        ]);
        
        return false; // Останавливаем дальнейшую обработку
    }
    
    return true;
}

private function generateCacheKey($bot): string
{
    $userId = $bot->getUserId;
    $messageText = $bot->getMessageText();
    
    return "bot_response:" . md5($userId . $messageText);
}
```

### Ограничение ресурсов

```php
private function resourceLimitMiddleware($bot, $parsed): bool
{
    // Проверяем использование памяти
    $memoryUsage = memory_get_usage(true);
    $maxMemory = 128 * 1024 * 1024; // 128MB
    
    if ($memoryUsage > $maxMemory) {
        $bot->sendSelf('🚨 Превышен лимит памяти. Попробуйте позже.');
        
        $this->logError('Memory limit exceeded', null, [
            'memory_usage' => $memoryUsage,
            'user_id' => $bot->getUserId,
        ]);
        
        return false;
    }
    
    // Проверяем время выполнения
    $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    $maxTime = 25; // 25 секунд
    
    if ($executionTime > $maxTime) {
        $bot->sendSelf('⏱️ Превышен лимит времени выполнения.');
        return false;
    }
    
    return true;
}
```

## Отладка middleware

### Middleware отладки

```php
private function debugMiddleware($bot, $parsed): bool
{
    if (!config('tegbot.debug')) {
        return true;
    }
    
    $debugInfo = [
        'middleware' => 'debug',
        'user_id' => $bot->getUserId,
        'message_type' => $bot->getMessageType(),
        'text_preview' => substr($bot->getMessageText() ?? '', 0, 50),
        'memory_usage' => memory_get_usage(true),
        'timestamp' => microtime(true),
    ];
    
    Log::debug('TegBot Debug Middleware', $debugInfo);
    
    // Отправляем debug info админам
    if ($this->shouldSendDebugToAdmins()) {
        $debugMessage = "🔍 **Debug Info**\n";
        $debugMessage .= "User: {$debugInfo['user_id']}\n";
        $debugMessage .= "Type: {$debugInfo['message_type']}\n";
        $debugMessage .= "Memory: " . $this->formatBytes($debugInfo['memory_usage']);
        
        $this->sendToAdmins($debugMessage);
    }
    
    return true;
}
```

## Примеры использования

### E-commerce бот

```php
public function main(): void
{
    $this->globalMiddleware([
        'spam_protection',
        'user_validation',
        'business_hours_check',
        'maintenance_mode_check',
    ]);
    
    $this->registerCommand('order', $callback, [
        'middleware' => [
            'customer_verification',
            'payment_method_check',
            'inventory_check',
        ],
    ]);
}
```

### Модерационный бот

```php
public function main(): void
{
    $this->globalMiddleware([
        'admin_authentication',
        'audit_logging',
        'rate_limiting',
    ]);
    
    $this->registerCommand('ban', $callback, [
        'middleware' => [
            'verify_target_user',
            'check_ban_permissions',
            'validate_ban_reason',
        ],
    ]);
}
```

### Служебный бот

```php
public function main(): void
{
    $this->globalMiddleware([
        'ip_whitelist_check',
        'api_key_validation',
        'request_throttling',
    ]);
    
    $this->registerCommand('deploy', $callback, [
        'middleware' => [
            'deployment_permissions',
            'system_health_check',
            'backup_verification',
        ],
    ]);
}
```

---

🔄 **Middleware TegBot** - максимальный контроль над обработкой сообщений! 