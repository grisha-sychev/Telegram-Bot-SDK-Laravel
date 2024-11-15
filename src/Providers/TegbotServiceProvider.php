<?php

namespace Teg\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * @package App\Providers
 */
class TegbotServiceProvider extends ServiceProvider
{
    /**
     * Maping services.
     */
    public function map(): void
    {
        $routePath = base_path('routes/tegbot.php');
        
        if (file_exists($routePath)) {
            Route::withoutMiddleware(['web', 'App\Http\Middleware\VerifyCsrfToken'])->group($routePath);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $routePath = base_path('routes/tegbot.php');

        $pathsToPublish = [
            __DIR__ . '/../../app' => app_path(),
            __DIR__ . '/../../config' => config_path(),
            __DIR__ . '/../../database' => database_path(),
            __DIR__ . '/../../routes' => base_path('routes'),
        ];

        $this->publishes($pathsToPublish, "tegbot");
        
        if (file_exists($routePath)) {
            $this->loadRoutesFrom($routePath);
        }

    }
}