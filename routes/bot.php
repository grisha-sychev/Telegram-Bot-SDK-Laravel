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
 * Использует middleware для проверки изоляции ботов
 */
Route::post('/webhook/{botName}', function ($botName) {
    try {
        // Получаем данные из middleware
        $botModel = request()->attributes->get('bot_model');
        $botEnvironment = request()->attributes->get('bot_environment');
        
        if (!$botModel || !$botEnvironment) {
            \Log::error("Bot: Missing bot data from middleware for bot: {$botName}");
            return response()->json(['error' => 'Bot isolation check failed'], 500);
        }

        // Логируем информацию о webhook
        \Log::info("Bot: Processing webhook for bot: {$botName}", [
            'bot_environment' => $botEnvironment,
            'webhook_url' => $botModel->getFullWebhookUrlForEnvironment($botEnvironment),
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
})->middleware('bot.isolation')->name('bot.webhook.modern'); 