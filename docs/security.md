# 🛡️ Безопасность TegBot

## Обзор системы безопасности

TegBot v2.0 включает многоуровневую систему безопасности:

- ✅ **Валидация Webhook**: Проверка подлинности запросов от Telegram
- ✅ **Защита от спама**: Ограничение частоты сообщений
- ✅ **Валидация входных данных**: Проверка структуры и типов данных
- ✅ **Контроль доступа**: Система разрешений и ролей
- ✅ **Безопасное логирование**: Фильтрация чувствительных данных
- ✅ **Rate Limiting**: Ограничение API запросов

## Настройка безопасности

### 1. Webhook Secret

```env
# Обязательно в продакшене!
TEGBOT_WEBHOOK_SECRET=your_secure_random_secret_32_chars
```

```php
// Генерация безопасного ключа
php artisan tinker
>>> Str::random(32)
```

### 2. Список администраторов

```env
# ID администраторов через запятую
TEGBOT_ADMIN_IDS=123456789,987654321
```

### 3. Настройка защиты от спама

```php
// config/tegbot.php
'security' => [
    'spam_protection' => [
        'enabled' => true,
        'max_messages_per_minute' => 20,
        'ban_duration_minutes' => 60,
        'whitelist_admin' => true,
    ],
],
```

## Автоматическая валидация

### safeMain() - Безопасный метод обработки

```php
// routes/tegbot.php
Route::post('/telegram/webhook', function () {
    $bot = new MyBot();
    return $bot->safeMain(); // Автоматическая валидация
});
```

`safeMain()` автоматически проверяет:
- Подлинность webhook запроса
- Структуру входящих данных
- Наличие обязательных полей
- Защиту от дублирования сообщений

### Проверка типов сообщений

```php
public function main(): void
{
    // Проверка наличия текста
    if (!$this->hasMessageText()) {
        $this->sendSelf('⚠️ Отправьте текстовое сообщение');
        return;
    }

    // Проверка команды
    if (!$this->isMessageCommand()) {
        $this->sendSelf('📝 Используйте команды, начинающиеся с /');
        return;
    }

    // Безопасная обработка команд
    $this->handleCommand($this->getMessageText);
}
```

## Система разрешений

### Ограничения по типу чата

```php
$this->registerCommand('admin', $callback, [
    'private_only' => true,  // Только в приватных чатах
    'group_only' => true,    // Только в группах
    'admin_only' => true,    // Только для админов
]);
```

### Пользовательские проверки

```php
$this->registerCommand('sensitive', $callback, [
    'middleware' => [
        function ($bot, $parsed) {
            // Проверка времени (рабочие часы)
            if (now()->hour < 9 || now()->hour > 18) {
                $bot->sendSelf('⏰ Команда доступна только в рабочее время');
                return false;
            }

            // Проверка роли пользователя
            if (!$bot->userHasRole('moderator')) {
                $bot->sendSelf('🚫 Недостаточно прав');
                return false;
            }

            return true;
        }
    ],
]);
```

## Защита от спама

### Встроенная защита

```php
// Автоматически включается в safeMain()
$this->globalMiddleware(['spam_protection']);
```

### Настраиваемые лимиты

```php
// config/tegbot.php
'security' => [
    'rate_limits' => [
        'global' => 100,        // сообщений в минуту глобально
        'per_user' => 20,       // сообщений в минуту на пользователя
        'per_chat' => 50,       // сообщений в минуту на чат
        'commands' => 10,       // команд в минуту на пользователя
    ],
],
```

### Кастомная защита от спама

```php
private function checkSpamProtection(): bool
{
    $userId = $this->getUserId;
    $key = "user_messages:{$userId}";
    
    $messages = Cache::get($key, 0);
    
    if ($messages > 50) { // 50 сообщений в час
        $this->sendSelf('🚫 Превышен лимит сообщений. Попробуйте позже.');
        return false;
    }
    
    Cache::put($key, $messages + 1, now()->addHour());
    return true;
}
```

## Валидация входных данных

### Проверка медиа файлов

```php
private function validateMediaFile($fileInfo): bool
{
    // Проверка размера файла
    if ($fileInfo['file_size'] > 20 * 1024 * 1024) { // 20MB
        $this->sendSelf('❌ Файл слишком большой (максимум 20MB)');
        return false;
    }

    // Проверка типа файла
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!in_array($fileInfo['mime_type'], $allowedTypes)) {
        $this->sendSelf('❌ Неподдерживаемый тип файла');
        return false;
    }

    return true;
}
```

### Фильтрация текста

```php
private function sanitizeUserInput(string $text): string
{
    // Удаление опасных символов
    $text = strip_tags($text);
    
    // Ограничение длины
    if (strlen($text) > 4000) {
        $text = substr($text, 0, 4000) . '...';
    }
    
    // Фильтрация плохих слов (если нужно)
    $badWords = ['spam', 'scam']; // расширьте список
    foreach ($badWords as $word) {
        $text = str_ireplace($word, str_repeat('*', strlen($word)), $text);
    }
    
    return trim($text);
}
```

## Безопасное логирование

### Фильтрация чувствительных данных

```php
public function logActivity(string $event, array $data = []): void
{
    // Удаляем чувствительные данные
    $filteredData = $this->filterSensitiveData($data);
    
    parent::logActivity($event, $filteredData);
}

private function filterSensitiveData(array $data): array
{
    $sensitive = ['password', 'token', 'secret', 'key', 'phone'];
    
    foreach ($sensitive as $field) {
        if (isset($data[$field])) {
            $data[$field] = '***FILTERED***';
        }
    }
    
    return $data;
}
```

### Структурированные логи

```php
$this->logActivity('user_action', [
    'action' => 'file_upload',
    'user_id' => $this->getUserId,
    'file_type' => $fileType,
    'file_size' => $fileSize,
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'timestamp' => now()->toISOString(),
]);
```

## Мониторинг безопасности

### Отслеживание подозрительной активности

```php
private function detectSuspiciousActivity(): void
{
    $userId = $this->getUserId;
    
    // Множество команд за короткое время
    $commands = Cache::get("commands:{$userId}", []);
    if (count($commands) > 10) { // 10 команд за минуту
        $this->alertAdmins("🚨 Подозрительная активность от пользователя {$userId}");
    }
    
    // Попытки доступа к админским командам
    if ($this->isAdminCommand() && !$this->isAdmin()) {
        $this->alertAdmins("⚠️ Попытка несанкционированного доступа от {$userId}");
    }
}

private function alertAdmins(string $message): void
{
    $adminIds = config('tegbot.security.admin_ids');
    
    foreach ($adminIds as $adminId) {
        $this->sendMessage($adminId, $message);
    }
}
```

### Автоматическое блокирование

```php
private function checkAndBlockUser(): bool
{
    $userId = $this->getUserId;
    $violations = Cache::get("violations:{$userId}", 0);
    
    if ($violations > 5) { // 5 нарушений = блокировка
        Cache::put("blocked:{$userId}", true, now()->addDay());
        $this->sendSelf('🚫 Вы заблокированы за нарушения');
        return false;
    }
    
    return true;
}
```

## IP-адреса Telegram

### Проверка источника запросов

```php
// middleware для проверки IP
private function validateTelegramIP(): bool
{
    $allowedIPs = [
        '149.154.160.0/20',
        '91.108.4.0/22',
    ];
    
    $clientIP = request()->ip();
    
    foreach ($allowedIPs as $allowedRange) {
        if ($this->ipInRange($clientIP, $allowedRange)) {
            return true;
        }
    }
    
    return false;
}
```

## HTTPS и SSL

### Обязательные требования

```nginx
# Nginx конфигурация
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    # Современные SSL настройки
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE+AESGCM:ECDHE+AES256:!aNULL:!MD5:!DSS;
    ssl_prefer_server_ciphers on;
    
    # HSTS
    add_header Strict-Transport-Security "max-age=31536000" always;
    
    location /telegram {
        # Ограничение доступа только для Telegram
        allow 149.154.160.0/20;
        allow 91.108.4.0/22;
        deny all;
        
        try_files $uri /index.php?$query_string;
    }
}
```

## Рекомендации по безопасности

### Для разработки

1. ✅ Используйте ngrok для тестирования webhook
2. ✅ Включите отладку: `TEGBOT_DEBUG=true`
3. ✅ Отключите кэширование в dev среде
4. ✅ Используйте тестовый бот, а не production

### Для продакшена

1. ✅ **Обязательно** установите `TEGBOT_WEBHOOK_SECRET`
2. ✅ Включите HTTPS с валидным SSL сертификатом
3. ✅ Ограничьте доступ к webhook по IP
4. ✅ Настройте мониторинг и alerting
5. ✅ Регулярно обновляйте пакет
6. ✅ Делайте регулярные backup
7. ✅ Используйте Redis для кэширования
8. ✅ Настройте rate limiting на уровне веб-сервера

### Чек-лист безопасности

- [ ] Webhook secret настроен
- [ ] HTTPS включен
- [ ] IP ограничения настроены  
- [ ] Спам-защита включена
- [ ] Админские ID указаны
- [ ] Логирование настроено
- [ ] Мониторинг работает
- [ ] Backup настроен
- [ ] SSL сертификат валиден
- [ ] Rate limiting настроен

## Реагирование на инциденты

### План действий при компрометации

1. **Немедленно**: Отключите webhook
2. **Смените** все секретные ключи
3. **Проверьте** логи на подозрительную активность
4. **Заблокируйте** скомпрометированных пользователей
5. **Обновите** пакет до последней версии
6. **Восстановите** из backup при необходимости

### Команды для экстренных случаев

```bash
# Отключение webhook
php artisan teg:webhook:delete

# Блокировка всех пользователей
php artisan teg:block-all

# Очистка всех кэшей
php artisan teg:cache:clear

# Экстренное восстановление
php artisan teg:emergency-restore
```

---

🔒 **Безопасность - это не опция, а необходимость!** 