<?php

namespace Teg\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * @package App\Providers
 */
class TegbotProvider extends ServiceProvider
{
    public function map()
    {
        $routePath = base_path('routes/tegbot.php');
        
        if (file_exists($routePath)) {
            Route::withoutMiddleware(['web', 'App\Http\Middleware\VerifyCsrfToken'])->group($routePath);
        }
    }
}
