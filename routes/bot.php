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
 */
Route::post('/webhook/{webhookUrl}', function ($webhookUrl) {
    try {
        // Ищем бота в базе данных
        $botModel = Bot::where('webhook_url', $webhookUrl)->where('enabled', true)->first();
        
        if (!$botModel) {
            return response()->json(['error' => 'Not found'], 404);
        }

        // Проверяем заголовок x-telegram-bot-api-secret-token
        $secretToken = request()->header('x-telegram-bot-api-secret-token');
        $expectedSecret = $botModel->webhook_secret;
        
        if ($secretToken !== $expectedSecret) {
            return response()->json(['error' => 'Not found'], 404);
        }

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
            $bot->setToken($botModel->token);
        }
        
        // Устанавливаем имя бота для получения токена из БД
        if (method_exists($bot, 'setBotName')) {
            $bot->setBotName($botModel->name);
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
        \Log::error("Bot: Error processing webhook for {$webhookUrl}: " . $e->getMessage(), [
            'bot' => $botModel->name,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json(['error' => 'Internal server error'], 500);
    }
})->name('bot.webhook'); 