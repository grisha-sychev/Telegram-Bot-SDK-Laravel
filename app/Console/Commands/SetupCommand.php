<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Bot;
use Illuminate\Support\Facades\DB;

class SetupCommand extends Command
{
    protected $signature = 'teg:set {--webhook= : Webhook URL} {--api-host= : Custom API host} {--no-ssl : Disable SSL verification} {--force : Force setup without confirmation}';
    protected $description = 'Настройка TegBot с поддержкой мультибота';

    public function handle()
    {
        $this->info('🚀 TegBot Multi-Bot Setup Wizard');
        $this->newLine();

        // Показываем существующие боты
        $this->showExistingBots();

        // Интерактивный ввод данных нового бота
        $botData = $this->collectBotData();
        if (!$botData) {
            return 1;
        }

        // Проверяем токен и получаем информацию о боте
        $apiHost = $this->option('api-host') ?: 'https://api.telegram.org';
        $noSsl = $botData['no_ssl'] ?? $this->option('no-ssl') ?? false;
        $botInfo = $this->getBotInfo($botData['token'], $apiHost, $noSsl);
        if (!$botInfo) {
            return 1;
        }

        // Дополняем данные информацией от Telegram
        $botData = array_merge($botData, [
            'username' => $botInfo['username'],
            'first_name' => $botInfo['first_name'],
            'description' => $botInfo['description'] ?? null,
            'bot_id' => $botInfo['id'],
        ]);

        $this->displayBotInfo($botInfo);

        // Сохраняем бота в базу данных
        $bot = $this->saveBotToDatabase($botData);
        if (!$bot) {
            return 1;
        }

        // Создаем класс бота если не существует
        $this->createBotClass($botData['name']);

        // Настраиваем webhook
        $webhookUrl = $botData['webhook_url'] ?: $this->option('webhook');
        if ($webhookUrl || $this->confirm('Настроить webhook?', true)) {
            $this->setupWebhook($bot, $apiHost, $webhookUrl, $noSsl);
        }

        // Проверяем и создаем конфигурацию
        $this->setupConfiguration();

        // Создаем директории
        $this->createDirectories();

        $this->newLine();
        $this->info('✅ Настройка TegBot завершена!');
        $this->line("🤖 Бот '{$bot->name}' успешно добавлен");
        $this->line('📖 Документация: vendor/tegbot/tegbot/docs/');
        $this->line('🔍 Проверка: php artisan teg:health');

        return 0;
    }

    private function showExistingBots(): void
    {
        try {
            $bots = Bot::all();
            if ($bots->isNotEmpty()) {
                $this->info('📋 Существующие боты:');
                $this->table(
                    ['ID', 'Имя', 'Username', 'Статус', 'Создан'],
                    $bots->map(function ($bot) {
                        return [
                            $bot->id,
                            $bot->name,
                            '@' . $bot->username,
                            $bot->enabled ? '✅ Активен' : '❌ Отключен',
                            $bot->created_at->format('d.m.Y H:i')
                        ];
                    })->toArray()
                );
                $this->newLine();
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Таблица ботов не найдена. Запустите миграции: php artisan migrate');
            $this->newLine();
        }
    }

    private function collectBotData(): ?array
    {
        $this->info('➕ Добавление нового бота');
        $this->newLine();

        // Запрашиваем имя бота
        $name = $this->ask('Введите имя бота (латинские буквы, без пробелов)');
        if (!$name) {
            $this->error('❌ Имя бота обязательно');
            return null;
        }

        // Проверяем имя на корректность
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $name)) {
            $this->error('❌ Имя бота должно начинаться с буквы и содержать только латинские буквы, цифры и подчеркивания');
            return null;
        }

        // Проверяем уникальность имени
        try {
            if (Bot::byName($name)->exists()) {
                $this->error("❌ Бот с именем '{$name}' уже существует");
                return null;
            }
        } catch (\Exception $e) {
            // Игнорируем ошибку если таблица не существует
        }

        // Запрашиваем токен
        $token = $this->ask('Введите токен бота (полученный от @BotFather)');
        if (!$token) {
            $this->error('❌ Токен бота обязателен');
            return null;
        }

        // Проверяем формат токена
        if (!preg_match('/^\d+:[A-Za-z0-9_-]{35}$/', $token)) {
            $this->error('❌ Неверный формат токена');
            $this->line('Токен должен иметь формат: 123456789:AABBccDDeeFFggHHiiJJkkLLmmNNooP');
            return null;
        }

        // Проверяем уникальность токена
        try {
            if (Bot::byToken($token)->exists()) {
                $this->error('❌ Бот с таким токеном уже существует');
                return null;
            }
        } catch (\Exception $e) {
            // Игнорируем ошибку если таблица не существует
        }

        // Запрашиваем администраторов (опционально)
        $adminIds = $this->ask('Введите ID администраторов через запятую (опционально)');
        $adminIdsArray = [];
        if ($adminIds) {
            $adminIdsArray = array_filter(array_map('trim', explode(',', $adminIds)));
            $adminIdsArray = array_map('intval', $adminIdsArray);
        }

        // Запрашиваем отключение SSL проверки (опционально)
        $noSsl = $this->option('no-ssl') ?: $this->confirm('Отключить проверку SSL сертификатов? (только для разработки)', false);

        // Запрашиваем домен для webhook (опционально) 
        $webhookUrl = $this->option('webhook');
        if (!$webhookUrl) {
            $defaultDomain = parse_url(url('/'), PHP_URL_SCHEME) . '://' . parse_url(url('/'), PHP_URL_HOST);
            $domain = $this->ask('Домен для webhook (Enter = текущий домен)', $defaultDomain);
            if ($domain) {
                $webhookUrl = rtrim($domain, '/') . "/webhook/{$name}";
            } else {
                $webhookUrl = null;
            }
        }

        return [
            'name' => $name,
            'token' => $token,
            'admin_ids' => $adminIdsArray,
            'enabled' => true,
            'webhook_url' => $webhookUrl,
            'no_ssl' => $noSsl,
        ];
    }

    private function getBotInfo(string $token, string $apiHost = 'https://api.telegram.org', bool $noSsl = false): ?array
    {
        $this->info('🔍 Проверка токена...');
        $this->line("  🌐 API хост: {$apiHost}");
        if ($noSsl) {
            $this->warn('  ⚠️  SSL проверка отключена');
        }

        try {
            $url = rtrim($apiHost, '/') . "/bot{$token}/getMe";

            $http = Http::timeout(10);
            if ($noSsl) {
                $http = $http->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ]
                ]);
            }

            $response = $http->get($url);

            if ($response->successful()) {
                $this->info('✅ Токен бота валиден');
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

    private function saveBotToDatabase(array $botData): ?Bot
    {
        $this->info('💾 Сохранение бота в базу данных...');

        try {
            $bot = Bot::create($botData);
            $this->info('✅ Бот сохранен в базу данных');
            return $bot;
        } catch (\Exception $e) {
            $this->error('❌ Ошибка сохранения в БД: ' . $e->getMessage());
            $this->warn('💡 Убедитесь что запущены миграции: php artisan migrate');
            return null;
        }
    }

    private function createBotClass(string $botName): void
    {
        $className = ucfirst($botName) . 'Bot';
        $classPath = app_path("Bots/{$className}.php");

        if (file_exists($classPath)) {
            $this->info("✅ Класс бота {$className} уже существует");
            return;
        }

        $this->info("📝 Создание класса бота {$className}...");

        // Создаем директорию если не существует
        $botsDir = app_path('Bots');
        if (!is_dir($botsDir)) {
            mkdir($botsDir, 0755, true);
        }

        // Шаблон класса бота
        $classTemplate = "<?php

namespace App\\Bots;

class {$className} extends AbstractBot
{
    public function main(): void
    {
        \$this->commands();
        // Обрабатываем команды
        if (\$this->hasMessageText() && \$this->isMessageCommand()) {
            \$this->handleCommand(\$this->getMessageText());
        }


        // Не обязательно, но рекомендуется, так как обработка автоматическая, будет просто игнорироваться
        \$this->fail(function () {
            \$this->sendSelf('❌ Ошибка'); // Или что то другое, на ваше усмотрение
        });
    }

    public function commands(): void
    {
        // Регистрируем команды, description не обязательно, но рекомендуется
        \$this->registerCommand('start', function () {
            \$this->sendSelf('🎉 Привет! Я бот {$botName}');
        }, [
            'description' => 'Запуск бота'
        ]);

        \$this->registerCommand('help', function () {
            \$this->sendSelf('📋 Доступные команды:\\n/start - Запуск бота\\n/help - Помощь');
        }, [
            'description' => 'Помощь'
        ]);
    }

}
";

        try {
            file_put_contents($classPath, $classTemplate);
            $this->info("✅ Класс бота создан: {$classPath}");
        } catch (\Exception $e) {
            $this->error("❌ Ошибка создания класса: {$e->getMessage()}");
        }
    }

    private function setupWebhook(Bot $bot, string $apiHost = 'https://api.telegram.org', string $webhookUrl = null, bool $noSsl = false): void
    {
        if (!$webhookUrl) {
            $defaultUrl = url("/webhook/{$bot->name}");
            $webhookUrl = $this->ask("Введите URL webhook", $defaultUrl);
        }

        if (!$webhookUrl) {
            $this->warn('⏭️  Пропускаем настройку webhook');
            return;
        }

        // Проверяем URL
        if (!filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            $this->error('❌ Неверный формат URL');
            return;
        }

        // Проверяем HTTPS (кроме локальных адресов или если указан --force)
        $isLocal = str_contains($webhookUrl, 'localhost') ||
            str_contains($webhookUrl, '127.0.0.1') ||
            str_contains($webhookUrl, '192.168.') ||
            str_contains($webhookUrl, '.local');

        if (!str_starts_with($webhookUrl, 'https://') && !$isLocal && !$this->option('force')) {
            $this->error('❌ URL должен быть HTTPS (используйте --force для обхода)');
            return;
        }

        if (!str_starts_with($webhookUrl, 'https://') && ($isLocal || $this->option('force'))) {
            $this->warn('⚠️  Используется HTTP соединение (только для разработки!)');
        }

        // Генерируем secret если не установлен
        $secret = $bot->webhook_secret ?? Str::random(32);

        // Устанавливаем webhook
        $this->info('🔧 Настройка webhook...');
        $this->line("  🌐 API хост: {$apiHost}");
        if ($noSsl) {
            $this->warn('  ⚠️  SSL проверка отключена');
        }

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

            $url = rtrim($apiHost, '/') . "/bot{$bot->token}/setWebhook";

            $http = Http::timeout(30);
            if ($noSsl) {
                $http = $http->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ]
                ]);
            }

            $response = $http->post($url, $payload);

            if ($response->successful()) {
                // Сохраняем webhook данные в БД
                $bot->update([
                    'webhook_url' => $webhookUrl,
                    'webhook_secret' => $secret,
                ]);

                $this->info('✅ Webhook настроен успешно');
                $this->line("  🌐 URL: {$webhookUrl}");
                $this->line("  🔐 Secret: {$secret}");
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