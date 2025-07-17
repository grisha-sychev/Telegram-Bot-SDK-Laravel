# 🎯 Система команд TegBot

## Обзор

TegBot v2.0 предоставляет мощную систему для работы с командами:

- 🚀 **Регистрация команд**: Простая и гибкая регистрация
- 📋 **Аргументы**: Поддержка обязательных и опциональных параметров
- 🔐 **Разрешения**: Контроль доступа и ограничения
- 📖 **Автоматическая справка**: Генерация help из описаний
- 🔄 **Middleware**: Промежуточные обработчики для команд
- 💡 **Интеллектуальный парсинг**: Поддержка кавычек и escape-символов

## Регистрация команд

### Базовая регистрация

```php
public function main(): void
{
    // Простая команда
    $this->registerCommand('start', function () {
        $this->start();
    });
    
    // Команда с описанием
    $this->registerCommand('help', function () {
        $this->showHelp();
    }, [
        'description' => 'Показать справку по командам',
    ]);
    
    // Автоматическая обработка команд
    if ($this->hasMessageText() && $this->isMessageCommand()) {
        $this->handleCommand($this->getMessageText);
    }
}
```

### Расширенная регистрация

```php
$this->registerCommand('ban', function ($args) {
    $this->banUser($args);
}, [
    'description' => 'Заблокировать пользователя',
    'args' => ['user_id', 'reason?', 'duration?'],
    'admin_only' => true,
    'private_only' => true,
    'middleware' => [
        'check_permissions',
        function ($bot, $parsed) {
            // Кастомная проверка
            if (!$bot->canBanUsers()) {
                $bot->sendSelf('❌ Недостаточно прав для блокировки');
                return false;
            }
            return true;
        }
    ],
]);
```

## Параметры команд

### Структура параметров

```php
[
    'description' => 'string',      // Описание команды для справки
    'args' => ['arg1', 'arg2?'],   // Аргументы (? = опциональный)
    'admin_only' => bool,          // Только для администраторов
    'private_only' => bool,        // Только в приватных чатах
    'group_only' => bool,          // Только в группах
    'middleware' => [              // Промежуточные обработчики
        'middleware_name',
        function ($bot, $parsed) { /* logic */ }
    ],
    'aliases' => ['alias1'],       // Альтернативные имена команды
    'hidden' => bool,              // Скрыть из справки
    'rate_limit' => int,           // Лимит вызовов в минуту
]
```

### Примеры параметров

```php
// Команда только для админов в приватном чате
$this->registerCommand('config', $callback, [
    'description' => 'Настройки бота',
    'admin_only' => true,
    'private_only' => true,
]);

// Команда с аргументами и алиасами
$this->registerCommand('search', $callback, [
    'description' => 'Поиск по каталогу',
    'args' => ['query', 'category?', 'limit?'],
    'aliases' => ['find', 's'],
]);

// Команда с лимитом вызовов
$this->registerCommand('weather', $callback, [
    'description' => 'Погода в городе',
    'args' => ['city'],
    'rate_limit' => 5, // 5 раз в минуту
]);
```

## Обработка аргументов

### Парсинг аргументов

```php
private function banUser(array $args): void
{
    // $args содержит распарсенные аргументы
    $userId = $args[0] ?? null;
    $reason = $args[1] ?? 'Нарушение правил';
    $duration = $args[2] ?? '24h';
    
    if (!$userId) {
        $this->sendSelf('❌ Укажите ID пользователя');
        return;
    }
    
    if (!is_numeric($userId)) {
        $this->sendSelf('❌ ID должен быть числом');
        return;
    }
    
    // Логика блокировки
    $this->performBan($userId, $reason, $duration);
}
```

### Продвинутый парсинг

```php
// Команда: /message 123456 "Привет мир" urgent
private function sendMessage(array $args): void
{
    $parsed = $this->parseCommandArgs($args, [
        'user_id' => 'required|numeric',
        'text' => 'required|string',
        'priority' => 'optional|in:normal,urgent,low',
    ]);
    
    if (!$parsed['valid']) {
        $this->sendSelf('❌ ' . $parsed['error']);
        return;
    }
    
    $userId = $parsed['args']['user_id'];
    $text = $parsed['args']['text'];
    $priority = $parsed['args']['priority'] ?? 'normal';
    
    // Отправка сообщения
    $this->sendMessage($userId, $text, $priority);
}
```

### Валидация аргументов

```php
public function parseCommandArgs(array $args, array $rules): array
{
    $result = ['valid' => true, 'args' => [], 'error' => null];
    $ruleKeys = array_keys($rules);
    
    foreach ($ruleKeys as $index => $key) {
        $rule = $rules[$key];
        $value = $args[$index] ?? null;
        
        // Проверка обязательных аргументов
        if (str_contains($rule, 'required') && $value === null) {
            $result['valid'] = false;
            $result['error'] = "Аргумент '{$key}' обязателен";
            break;
        }
        
        // Валидация типов
        if ($value !== null) {
            if (str_contains($rule, 'numeric') && !is_numeric($value)) {
                $result['valid'] = false;
                $result['error'] = "Аргумент '{$key}' должен быть числом";
                break;
            }
            
            if (str_contains($rule, 'in:')) {
                preg_match('/in:([^|]+)/', $rule, $matches);
                $allowedValues = explode(',', $matches[1]);
                if (!in_array($value, $allowedValues)) {
                    $result['valid'] = false;
                    $result['error'] = "Аргумент '{$key}' должен быть одним из: " . implode(', ', $allowedValues);
                    break;
                }
            }
        }
        
        $result['args'][$key] = $value;
    }
    
    return $result;
}
```

## Система разрешений

### Встроенные проверки

```php
// Проверка администратора
$this->registerCommand('admin', $callback, [
    'admin_only' => true,
]);

// Проверка типа чата
$this->registerCommand('private', $callback, [
    'private_only' => true,
]);

$this->registerCommand('group', $callback, [
    'group_only' => true,
]);
```

### Кастомные проверки разрешений

```php
private function checkModerator($bot, $parsed): bool
{
    $userId = $bot->getUserId;
    
    // Проверка в базе данных
    $user = User::find($userId);
    
    if (!$user || !$user->hasRole('moderator')) {
        $bot->sendSelf('🚫 Эта команда доступна только модераторам');
        return false;
    }
    
    return true;
}

private function checkBusinessHours($bot, $parsed): bool
{
    $hour = now()->hour;
    
    if ($hour < 9 || $hour > 18) {
        $bot->sendSelf('⏰ Команда доступна только в рабочее время (9:00-18:00)');
        return false;
    }
    
    return true;
}

// Использование
$this->registerCommand('report', $callback, [
    'middleware' => [
        [$this, 'checkModerator'],
        [$this, 'checkBusinessHours'],
    ],
]);
```

## Автоматическая справка

### Генерация справки

```php
private function showHelp(): void
{
    $helpText = $this->generateHelp();
    $this->sendSelf($helpText);
}

// Результат generateHelp():
// 🤖 **Справка по командам**
//
// /start - Запуск бота
// /help - Показать справку по командам  
// /search <query> [category] [limit] - Поиск по каталогу
// /ban <user_id> [reason] [duration] - Заблокировать пользователя (только админы)
//
// 💡 *<> - обязательный параметр, [] - опциональный*
```

### Кастомная справка

```php
private function showCustomHelp(): void
{
    $commands = $this->getRegisteredCommands();
    $helpText = "🎯 **Доступные команды**\n\n";
    
    $categories = [
        'Основные' => ['start', 'help', 'info'],
        'Поиск' => ['search', 'find'],
        'Администрирование' => ['ban', 'unban', 'config'],
    ];
    
    foreach ($categories as $category => $commandList) {
        $helpText .= "**{$category}:**\n";
        
        foreach ($commandList as $cmd) {
            if (isset($commands[$cmd])) {
                $command = $commands[$cmd];
                $helpText .= "/{$cmd}";
                
                if (!empty($command['args'])) {
                    foreach ($command['args'] as $arg) {
                        if (str_ends_with($arg, '?')) {
                            $helpText .= " [" . rtrim($arg, '?') . "]";
                        } else {
                            $helpText .= " <{$arg}>";
                        }
                    }
                }
                
                $helpText .= " - " . ($command['description'] ?? 'Описание отсутствует') . "\n";
            }
        }
        
        $helpText .= "\n";
    }
    
    $this->sendSelf($helpText);
}
```

## Обработка команд

### Основной обработчик

```php
public function handleCommand(string $commandText): void
{
    // Парсинг команды
    $parts = $this->parseCommand($commandText);
    $command = $parts['command'];
    $args = $parts['args'];
    
    // Проверка существования команды
    if (!$this->commandExists($command)) {
        $this->sendSelf("❌ Неизвестная команда: /{$command}");
        return;
    }
    
    // Получение информации о команде
    $commandInfo = $this->getCommandInfo($command);
    
    // Проверка разрешений
    if (!$this->checkCommandPermissions($commandInfo)) {
        return; // Сообщение об ошибке уже отправлено
    }
    
    // Выполнение middleware
    if (!$this->runCommandMiddleware($commandInfo, $args)) {
        return; // Middleware заблокировал выполнение
    }
    
    // Проверка rate limiting
    if (!$this->checkRateLimit($command, $commandInfo)) {
        return;
    }
    
    // Выполнение команды
    try {
        $callback = $commandInfo['callback'];
        call_user_func($callback, $args);
        
        // Логирование выполнения
        $this->logActivity('command_executed', [
            'command' => $command,
            'args_count' => count($args),
            'user_id' => $this->getUserId,
        ]);
        
    } catch (Exception $e) {
        $this->logError('Command execution failed', $e, [
            'command' => $command,
            'args' => $args,
        ]);
        
        $this->sendSelf('💥 Произошла ошибка при выполнении команды');
    }
}
```

### Парсинг команд

```php
private function parseCommand(string $commandText): array
{
    // Удаляем начальный /
    $commandText = ltrim($commandText, '/');
    
    // Разбиваем на части с учетом кавычек
    $parts = $this->parseCommandArguments($commandText);
    $command = array_shift($parts);
    
    return [
        'command' => strtolower($command),
        'args' => $parts,
    ];
}

private function parseCommandArguments(string $input): array
{
    $args = [];
    $current = '';
    $inQuotes = false;
    $quoteChar = null;
    
    for ($i = 0; $i < strlen($input); $i++) {
        $char = $input[$i];
        
        if (($char === '"' || $char === "'") && !$inQuotes) {
            $inQuotes = true;
            $quoteChar = $char;
        } elseif ($char === $quoteChar && $inQuotes) {
            $inQuotes = false;
            $quoteChar = null;
        } elseif ($char === ' ' && !$inQuotes) {
            if ($current !== '') {
                $args[] = $current;
                $current = '';
            }
        } else {
            $current .= $char;
        }
    }
    
    if ($current !== '') {
        $args[] = $current;
    }
    
    return $args;
}
```

## Rate Limiting

### Ограничение частоты команд

```php
private function checkRateLimit(string $command, array $commandInfo): bool
{
    if (!isset($commandInfo['rate_limit'])) {
        return true;
    }
    
    $limit = $commandInfo['rate_limit'];
    $userId = $this->getUserId;
    $key = "rate_limit:{$command}:{$userId}";
    
    $current = Cache::get($key, 0);
    
    if ($current >= $limit) {
        $this->sendSelf("⏱️ Превышен лимит команды /{$command} ({$limit} раз в минуту)");
        return false;
    }
    
    Cache::put($key, $current + 1, now()->addMinute());
    return true;
}
```

### Глобальное ограничение команд

```php
private function checkGlobalRateLimit(): bool
{
    $userId = $this->getUserId;
    $key = "global_commands:{$userId}";
    $maxCommands = config('tegbot.security.max_commands_per_minute', 30);
    
    $commandCount = Cache::get($key, 0);
    
    if ($commandCount >= $maxCommands) {
        $this->sendSelf('🚫 Превышен лимит команд. Попробуйте позже.');
        return false;
    }
    
    Cache::put($key, $commandCount + 1, now()->addMinute());
    return true;
}
```

## Примеры команд

### Команда с поиском

```php
$this->registerCommand('search', function ($args) {
    $this->handleSearch($args);
}, [
    'description' => 'Поиск товаров',
    'args' => ['query', 'category?', 'limit?'],
]);

private function handleSearch(array $args): void
{
    $query = $args[0] ?? null;
    $category = $args[1] ?? null;
    $limit = isset($args[2]) ? (int)$args[2] : 10;
    
    if (!$query) {
        $this->sendSelf('❌ Укажите поисковый запрос');
        return;
    }
    
    if ($limit > 50) {
        $limit = 50;
        $this->sendSelf('⚠️ Максимальный лимит результатов: 50');
    }
    
    $this->sendSelf("🔍 Ищу: {$query}" . ($category ? " в категории {$category}" : ""));
    
    // Логика поиска
    $results = $this->performSearch($query, $category, $limit);
    
    if (empty($results)) {
        $this->sendSelf('😔 Ничего не найдено');
        return;
    }
    
    $this->displaySearchResults($results);
}
```

### Команда с настройками

```php
$this->registerCommand('settings', function ($args) {
    $this->handleSettings($args);
}, [
    'description' => 'Управление настройками',
    'args' => ['action?', 'key?', 'value?'],
    'private_only' => true,
]);

private function handleSettings(array $args): void
{
    $action = $args[0] ?? 'show';
    
    switch ($action) {
        case 'show':
            $this->showUserSettings();
            break;
        case 'set':
            $this->setUserSetting($args[1] ?? null, $args[2] ?? null);
            break;
        case 'reset':
            $this->resetUserSettings();
            break;
        default:
            $this->sendSelf("❌ Неизвестное действие: {$action}\nИспользуйте: show, set, reset");
    }
}
```

### Административные команды

```php
$this->registerCommand('admin', function ($args) {
    $this->handleAdminPanel($args);
}, [
    'description' => 'Панель администратора',
    'args' => ['action?'],
    'admin_only' => true,
    'private_only' => true,
]);

private function handleAdminPanel(array $args): void
{
    $action = $args[0] ?? 'menu';
    
    switch ($action) {
        case 'menu':
            $this->showAdminMenu();
            break;
        case 'stats':
            $this->showBotStats();
            break;
        case 'users':
            $this->showUsersList();
            break;
        case 'logs':
            $this->showRecentLogs();
            break;
        default:
            $this->sendSelf("❌ Неизвестная команда: {$action}");
    }
}

private function showAdminMenu(): void
{
    $message = "🔧 **Панель администратора**\n\n";
    $message .= "Доступные команды:\n";
    $message .= "/admin stats - Статистика бота\n";
    $message .= "/admin users - Список пользователей\n";
    $message .= "/admin logs - Последние логи\n";
    
    $this->sendSelfInline($message, [
        ['callback:admin_stats', '📊 Статистика'],
        ['callback:admin_users', '👥 Пользователи'],
        ['callback:admin_logs', '📝 Логи'],
    ]);
}
```

## Debugging команд

### Отладочная информация

```php
private function debugCommand(string $command, array $args): void
{
    if (!config('tegbot.debug')) {
        return;
    }
    
    $debugInfo = [
        'command' => $command,
        'args' => $args,
        'user_id' => $this->getUserId,
        'chat_type' => $this->getChatType(),
        'timestamp' => now()->toISOString(),
    ];
    
    $this->logActivity('command_debug', $debugInfo);
    
    // Отправка отладочной информации админам
    if ($this->isDebugMode()) {
        $debugMessage = "🐛 **Debug Info**\n";
        $debugMessage .= "Command: /{$command}\n";
        $debugMessage .= "Args: " . json_encode($args) . "\n";
        $debugMessage .= "User: {$this->getUserId}\n";
        
        $this->sendToAdmins($debugMessage);
    }
}
```

---

🎯 **Система команд TegBot** - максимальная гибкость и контроль! 