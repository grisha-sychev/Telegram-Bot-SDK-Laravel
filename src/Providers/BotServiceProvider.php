<?php

namespace Bot\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
// use Bot\Console\Commands\HealthCommand;
// use Bot\Console\Commands\SetupCommand;
// use Bot\Console\Commands\ConfigCommand;
// use Bot\Console\Commands\StatsCommand;
// use Bot\Console\Commands\WebhookCommand;
// use Bot\Console\Commands\MigrateCommand;

/**
 * Bot Service Provider
 * Регистрирует команды, конфигурацию и ресурсы пакета
 */
class BotServiceProvider extends ServiceProvider
{
    /**
     * All console commands.
     */
    protected $commands = [
        // Команды временно отключены до публикации в приложение
        // HealthCommand::class,
        // SetupCommand::class,
        // ConfigCommand::class,
        // StatsCommand::class,
        // WebhookCommand::class,
        // MigrateCommand::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрируем конфигурацию
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/bot.php',
            'bot'
        );

        // Регистрируем команды только для консоли
        if ($this->app->runningInConsole() && !empty($this->commands)) {
            $this->commands($this->commands);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->bootPublishing();
        $this->bootRoutes();
    }

    /**
     * Setup publishing of package resources.
     */
    protected function bootPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            // Конфигурация
            $this->publishes([
                __DIR__ . '/../../config/bot.php' => config_path('bot.php'),
            ], ['bot-config', 'config']);

            // Файлы приложения (боты, команды)
            $this->publishes([
                __DIR__ . '/../../app' => app_path(),
            ], ['bot-app', 'app']);

            // Маршруты
            $this->publishes([
                __DIR__ . '/../../routes' => base_path('routes'),
            ], ['bot-routes', 'routes']);

            // Миграции
            $this->publishes([
                __DIR__ . '/../../database' => database_path(),
            ], ['bot-database', 'database', 'migrations']);

            // Документация
            $this->publishes([
                __DIR__ . '/../../docs' => base_path('docs/bot'),
            ], ['bot-docs', 'docs']);

            // Все файлы сразу
            $pathsToPublish = [
                __DIR__ . '/../../app' => app_path(),
                __DIR__ . '/../../config' => config_path(),
                __DIR__ . '/../../database' => database_path(),
                __DIR__ . '/../../routes' => base_path('routes'),
            ];

            $this->publishes($pathsToPublish, 'bot');
        }
    }

    /**
     * Setup route loading.
     */
    protected function bootRoutes(): void
    {
        $this->map();

        $route = base_path('routes/bot.php');

        if (file_exists($route)) {
            $this->loadRoutesFrom($route);
        }
    }

    /**
     * Map services.
     */
    public function map(): void
    {
        $route = base_path('routes/bot.php');

        if (file_exists($route)) {
            Route::withoutMiddleware(['web', 'App\Http\Middleware\VerifyCsrfToken'])->group($route);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return $this->commands;
    }
} 