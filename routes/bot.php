<?php

use Illuminate\Support\Facades\Route;
use App\Models\Bot;

/*
|--------------------------------------------------------------------------
| Bot Webhook Routes  
|--------------------------------------------------------------------------
*/

/**
 * Роут для мультиботной архитектуры с базой данных
 * URL: /webhook/{botName}
 * Боты загружаются из базы данных
 * Автоматически использует домен текущего окружения (APP_ENV)
 */
Route::post('/webhook/{botName}', function ($botName) {
    try {
        // Ищем бота в базе данных
        $botModel = Bot::byName($botName)->where('enabled', true)->first();
        
        if (!$botModel) {
            \Log::warning("Bot: Bot not found or disabled: {$botName}");
            return response()->json(['error' => 'Bot not found or disabled'], 404);
        }

        // Проверяем, что у бота есть webhook URL и домен для текущего окружения
        $currentEnvironment = Bot::getCurrentEnvironment();
        $fullWebhookUrl = $botModel->getFullWebhookUrl();
        
        if (!$fullWebhookUrl) {
            \Log::error("Bot: No webhook URL or domain for environment '{$currentEnvironment}' for bot: {$botName}");
            return response()->json(['error' => 'Webhook not configured for current environment'], 500);
        }

        // Определяем окружение для этого бота на основе домена
        $currentEnvironment = Bot::getCurrentEnvironment();
        $botEnvironment = (function($botModel, $currentEnvironment) {
            // Получаем домен из запроса
            $requestDomain = request()->getHost();
            
            // Проверяем, соответствует ли домен запроса dev или prod домену бота
            $devDomain = $botModel->dev_domain;
            $prodDomain = $botModel->prod_domain;
            
            if ($devDomain) {
                $devHost = parse_url($devDomain, PHP_URL_HOST);
                if ($devHost && $requestDomain === $devHost) {
                    return 'dev';
                }
            }
            
            if ($prodDomain) {
                $prodHost = parse_url($prodDomain, PHP_URL_HOST);
                if ($prodHost && $requestDomain === $prodHost) {
                    return 'prod';
                }
            }
            
            // Если домен не соответствует ни одному из настроенных, используем текущее окружение
            return $currentEnvironment;
        })($botModel, $currentEnvironment);
        
        // Устанавливаем окружение для бота
        Bot::setCurrentEnvironment($botEnvironment);
        
        // Логируем информацию о webhook
        \Log::info("Bot: Processing webhook for bot: {$botName}", [
            'request_environment' => $currentEnvironment,
            'bot_environment' => $botEnvironment,
            'webhook_url' => $fullWebhookUrl,
            'domain' => $botModel->getDomainForEnvironment($botEnvironment),
            'request_domain' => request()->getHost()
        ]);

        // Формируем имя класса бота
        $class = $botModel->getBotClass();

        if (!class_exists($class)) {
            \Log::error("Bot: Bot class not found: {$class}");
            return response()->json(['error' => 'Bot class not found'], 404);
        }
        
        // Создаем экземпляр бота
        $bot = new $class();
        
        // Устанавливаем токен для LightBot
        if (method_exists($bot, 'setToken')) {
            $bot->setToken($botModel->getTokenForEnvironment($botEnvironment));
        }
        
        // Устанавливаем имя бота для получения токена из БД
        if (method_exists($bot, 'setBotName')) {
            $bot->setBotName($botName);
        }
        
        // Устанавливаем дополнительные настройки если метод существует
        if (method_exists($bot, 'setBotModel')) {
            $bot->setBotModel($botModel);
        }
        
        // Запускаем обработку (приоритет: safeMain > run > main)
        if (method_exists($bot, 'safeMain')) {
            return $bot->safeMain();
        } elseif (method_exists($bot, 'run')) {
            return $bot->run()->main();
        } elseif (method_exists($bot, 'main')) {
            return $bot->main();
        } else {
            \Log::error("Bot: Bot {$class} has no safeMain(), main() or run() method");
            return response()->json(['error' => 'Bot method not found'], 500);
        }
        
    } catch (\Exception $e) {
        \Log::error("Bot: Error processing webhook for {$botName}: " . $e->getMessage(), [
            'bot' => $botName,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json(['error' => 'Internal server error'], 500);
    }
})->name('bot.webhook.modern'); 