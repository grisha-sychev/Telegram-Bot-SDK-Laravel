<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bot;

class EnvironmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:env {environment : Окружение (dev/prod)} {--reset : Сбросить к значению из env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Переключить окружение для ботов (dev/prod)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $environment = $this->argument('environment');
        $reset = $this->option('reset');

        if ($reset) {
            Bot::resetCurrentEnvironment();
            $currentEnv = Bot::getCurrentEnvironment();
            $this->info("✅ Окружение сброшено к значению из env: {$currentEnv}");
            return 0;
        }

        if (!in_array($environment, ['dev', 'prod'])) {
            $this->error('❌ Окружение должно быть "dev" или "prod"');
            return 1;
        }

        // Устанавливаем новое окружение
        Bot::setCurrentEnvironment($environment);
        
        $this->info("✅ Окружение переключено на: {$environment}");
        
        // Показываем активных ботов для нового окружения
        $this->showActiveBots($environment);
        
        return 0;
    }

    /**
     * Показать активных ботов для указанного окружения
     */
    private function showActiveBots(string $environment): void
    {
        $this->newLine();
        $this->info("🤖 Активные боты для окружения '{$environment}':");
        
        $bots = Bot::enabled()
            ->withTokenForEnvironment($environment)
            ->withDomainForEnvironment($environment)
            ->get();
        
        if ($bots->isEmpty()) {
            $this->warn("⚠️  Нет активных ботов для окружения '{$environment}'");
            return;
        }
        
        $table = [];
        foreach ($bots as $bot) {
            $table[] = [
                $bot->name,
                $bot->getMaskedTokenForEnvironment($environment),
                $bot->getDomainForEnvironment($environment),
                $bot->webhook_url ?: 'Не настроен'
            ];
        }
        
        $this->table(
            ['Имя', 'Токен', 'Домен', 'Webhook URL'],
            $table
        );
    }
} 