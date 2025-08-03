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
Route::post('/webhook/{botName}', function ($botName) {
    try {
        // Ищем бота в базе данных
        $botModel = Bot::byName($botName)->where('enabled', true)->first();
        
        if (!$botModel) {
            \Log::error("Bot: Bot not found or disabled: {$botName}");
            return response()->json(['error' => 'Bot not found or disabled'], 404);
        }

        // Логируем информацию о webhook
        \Log::info("Bot: Processing webhook for bot: {$botName}", [
            'webhook_url' => $botModel->webhook_url,
            'full_webhook_url' => $botModel->getFullWebhookUrl()
        ]);

        // Проверяем, что у бота есть токен
        if (!$botModel->hasToken()) {
            \Log::error("Bot: No token for bot: {$botName}");
            return response()->json(['error' => 'Bot not configured'], 403);
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
})->name('bot.webhook'); 