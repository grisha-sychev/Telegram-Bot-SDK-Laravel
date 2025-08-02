<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bot;

class IsolationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:isolation {bot? : Имя бота для проверки} {--env=* : Окружения для проверки}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка изоляции ботов между окружениями';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $botName = $this->argument('bot');
        $environments = $this->option('env');
        
        if (empty($environments)) {
            $environments = ['dev', 'prod'];
        }
        
        $this->info('🔒 Проверка изоляции ботов...');
        
        if ($botName) {
            $this->checkBotIsolation($botName, $environments);
        } else {
            $this->checkAllBotsIsolation($environments);
        }
        
        return 0;
    }
    
    /**
     * Проверка изоляции конкретного бота
     */
    private function checkBotIsolation(string $botName, array $environments): void
    {
        $bot = Bot::byName($botName)->first();
        
        if (!$bot) {
            $this->error("❌ Бот '{$botName}' не найден");
            return;
        }
        
        $this->info("🤖 Проверка изоляции бота: {$botName}");
        
        foreach ($environments as $env) {
            $this->checkEnvironmentIsolation($bot, $env);
        }
    }
    
    /**
     * Проверка изоляции всех ботов
     */
    private function checkAllBotsIsolation(array $environments): void
    {
        $bots = Bot::enabled()->get();
        
        if ($bots->isEmpty()) {
            $this->warn("⚠️  Нет активных ботов");
            return;
        }
        
        foreach ($environments as $env) {
            $this->info("🌍 Проверка окружения: {$env}");
            
            $isolatedBots = [];
            $nonIsolatedBots = [];
            
            foreach ($bots as $bot) {
                if ($bot->isIsolatedForEnvironment($env)) {
                    $isolatedBots[] = $bot;
                } else {
                    $nonIsolatedBots[] = $bot;
                }
            }
            
            // Показываем изолированных ботов
            if (!empty($isolatedBots)) {
                $this->info("✅ Изолированные боты для '{$env}':");
                $table = [];
                foreach ($isolatedBots as $bot) {
                    $table[] = [
                        $bot->name,
                        $bot->getMaskedTokenForEnvironment($env),
                        $bot->getDomainForEnvironment($env)
                    ];
                }
                $this->table(['Имя', 'Токен', 'Домен'], $table);
            }
            
            // Показываем не изолированных ботов
            if (!empty($nonIsolatedBots)) {
                $this->warn("⚠️  Не изолированные боты для '{$env}':");
                $table = [];
                foreach ($nonIsolatedBots as $bot) {
                    $hasToken = $bot->hasTokenForEnvironment($env) ? '✅' : '❌';
                    $hasDomain = $bot->hasDomainForEnvironment($env) ? '✅' : '❌';
                    $table[] = [
                        $bot->name,
                        $hasToken,
                        $hasDomain,
                        $bot->getMaskedTokenForEnvironment($env) ?: 'Нет токена',
                        $bot->getDomainForEnvironment($env) ?: 'Нет домена'
                    ];
                }
                $this->table(['Имя', 'Токен', 'Домен', 'Токен', 'Домен'], $table);
            }
            
            $this->newLine();
        }
    }
    
    /**
     * Проверка изоляции бота для конкретного окружения
     */
    private function checkEnvironmentIsolation(Bot $bot, string $environment): void
    {
        $this->line("  🔍 Проверка окружения '{$environment}':");
        
        $hasToken = $bot->hasTokenForEnvironment($environment);
        $hasDomain = $bot->hasDomainForEnvironment($environment);
        $isIsolated = $bot->isIsolatedForEnvironment($environment);
        
        $this->line("    Токен: " . ($hasToken ? '✅' : '❌'));
        $this->line("    Домен: " . ($hasDomain ? '✅' : '❌'));
        $this->line("    Изолирован: " . ($isIsolated ? '✅' : '❌'));
        
        if ($hasToken) {
            $this->line("    Токен: " . $bot->getMaskedTokenForEnvironment($environment));
        }
        
        if ($hasDomain) {
            $this->line("    Домен: " . $bot->getDomainForEnvironment($environment));
        }
        
        if (!$isIsolated) {
            $this->warn("    ⚠️  Бот не изолирован для окружения '{$environment}'");
        }
        
        $this->newLine();
    }
} 