<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bot;

class SetupIsolationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:setup-isolation 
                            {bot? : Имя бота для настройки}
                            {--env=* : Окружения для настройки}
                            {--fix : Автоматически исправить проблемы изоляции}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Настройка изоляции ботов между окружениями';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $botName = $this->argument('bot');
        $environments = $this->option('env');
        $fix = $this->option('fix');
        
        if (empty($environments)) {
            $environments = ['dev', 'prod'];
        }
        
        $this->info('🔒 Настройка изоляции ботов...');
        
        if ($botName) {
            $this->setupBotIsolation($botName, $environments, $fix);
        } else {
            $this->setupAllBotsIsolation($environments, $fix);
        }
        
        return 0;
    }
    
    /**
     * Настройка изоляции конкретного бота
     */
    private function setupBotIsolation(string $botName, array $environments, bool $fix): void
    {
        $bot = Bot::byName($botName)->first();
        
        if (!$bot) {
            $this->error("❌ Бот '{$botName}' не найден");
            return;
        }
        
        $this->info("🤖 Настройка изоляции бота: {$botName}");
        
        foreach ($environments as $env) {
            $this->setupEnvironmentIsolation($bot, $env, $fix);
        }
    }
    
    /**
     * Настройка изоляции всех ботов
     */
    private function setupAllBotsIsolation(array $environments, bool $fix): void
    {
        $bots = Bot::enabled()->get();
        
        if ($bots->isEmpty()) {
            $this->warn("⚠️  Нет активных ботов");
            return;
        }
        
        foreach ($environments as $env) {
            $this->info("🌍 Настройка окружения: {$env}");
            
            $isolatedBots = [];
            $nonIsolatedBots = [];
            
            foreach ($bots as $bot) {
                if ($bot->isFullyIsolatedForEnvironment($env)) {
                    $isolatedBots[] = $bot;
                } else {
                    $nonIsolatedBots[] = $bot;
                }
            }
            
            // Показываем изолированных ботов
            if (!empty($isolatedBots)) {
                $this->info("✅ Полностью изолированные боты для '{$env}':");
                $table = [];
                foreach ($isolatedBots as $bot) {
                    $details = $bot->getIsolationDetailsForEnvironment($env);
                    $table[] = [
                        $bot->name,
                        $details['token'],
                        $details['domain'],
                        $details['webhook_url']
                    ];
                }
                $this->table(['Имя', 'Токен', 'Домен', 'Webhook URL'], $table);
            }
            
            // Показываем не изолированных ботов
            if (!empty($nonIsolatedBots)) {
                $this->warn("⚠️  Не изолированные боты для '{$env}':");
                $table = [];
                foreach ($nonIsolatedBots as $bot) {
                    $details = $bot->getIsolationDetailsForEnvironment($env);
                    $table[] = [
                        $bot->name,
                        $details['has_token'] ? '✅' : '❌',
                        $details['has_domain'] ? '✅' : '❌',
                        $details['webhook_isolated'] ? '✅' : '❌',
                        $details['token'],
                        $details['domain'],
                        $details['webhook_url'] ?: 'Нет webhook URL'
                    ];
                }
                $this->table(['Имя', 'Токен', 'Домен', 'Webhook', 'Токен', 'Домен', 'Webhook URL'], $table);
                
                if ($fix) {
                    $this->fixIsolationIssues($nonIsolatedBots, $env);
                }
            }
            
            $this->newLine();
        }
    }
    
    /**
     * Настройка изоляции бота для конкретного окружения
     */
    private function setupEnvironmentIsolation(Bot $bot, string $environment, bool $fix): void
    {
        $this->line("  🔍 Настройка окружения '{$environment}':");
        
        $details = $bot->getIsolationDetailsForEnvironment($environment);
        
        $this->line("    Токен: " . ($details['has_token'] ? '✅' : '❌'));
        $this->line("    Домен: " . ($details['has_domain'] ? '✅' : '❌'));
        $this->line("    Webhook изолирован: " . ($details['webhook_isolated'] ? '✅' : '❌'));
        $this->line("    Полностью изолирован: " . ($details['fully_isolated'] ? '✅' : '❌'));
        
        if ($details['has_token']) {
            $this->line("    Токен: " . $details['token']);
        }
        
        if ($details['has_domain']) {
            $this->line("    Домен: " . $details['domain']);
        }
        
        if ($details['webhook_url']) {
            $this->line("    Webhook URL: " . $details['webhook_url']);
        }
        
        if (!empty($details['conflicting_bots'])) {
            $this->warn("    ⚠️  Конфликтующие боты: " . implode(', ', $details['conflicting_bots']));
        }
        
        if (!$details['fully_isolated'] && $fix) {
            $this->fixBotIsolation($bot, $environment);
        }
        
        $this->newLine();
    }
    
    /**
     * Исправить проблемы изоляции для бота
     */
    private function fixBotIsolation(Bot $bot, string $environment): void
    {
        $this->line("    🔧 Исправление проблем изоляции...");
        
        $details = $bot->getIsolationDetailsForEnvironment($environment);
        
        // Если нет токена
        if (!$details['has_token']) {
            $token = $this->ask("    Введите токен для окружения '{$environment}':");
            if ($token) {
                $bot->setTokenForEnvironment($environment, $token);
                $this->line("    ✅ Токен установлен");
            }
        }
        
        // Если нет домена
        if (!$details['has_domain']) {
            $domain = $this->ask("    Введите домен для окружения '{$environment}':");
            if ($domain) {
                $bot->setDomainForEnvironment($environment, $domain);
                $this->line("    ✅ Домен установлен");
            }
        }
        
        // Если нет webhook URL
        if (!$details['has_webhook']) {
            $webhookUrl = $this->ask("    Введите webhook URL (например, /webhook/AgentShop):");
            if ($webhookUrl) {
                $bot->webhook_url = $webhookUrl;
                $this->line("    ✅ Webhook URL установлен");
            }
        }
        
        // Сохраняем изменения
        if ($bot->isDirty()) {
            $bot->save();
            $this->line("    ✅ Изменения сохранены");
        }
    }
    
    /**
     * Исправить проблемы изоляции для всех ботов
     */
    private function fixIsolationIssues(\Illuminate\Database\Eloquent\Collection $bots, string $environment): void
    {
        $this->info("🔧 Автоматическое исправление проблем изоляции для '{$environment}'...");
        
        foreach ($bots as $bot) {
            $details = $bot->getIsolationDetailsForEnvironment($environment);
            
            if (!$details['fully_isolated']) {
                $this->line("  Исправление бота: {$bot->name}");
                $this->fixBotIsolation($bot, $environment);
            }
        }
    }
} 