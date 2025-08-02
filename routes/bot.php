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
 * URL: /webhook/{botName} или /webhook/{botName}dev
 * Боты загружаются из базы данных
 * Автоматически определяет окружение по суффиксу "dev" в URL
 */
Route::post('/webhook/{botName}', function ($botName) {
    try {
        // Ищем бота в базе данных
        $botModel = Bot::byName($botName)->where('enabled', true)->first();
        
        if (!$botModel) {
            \Log::error("Bot: Bot not found or disabled: {$botName}");
            return response()->json(['error' => 'Bot not found or disabled'], 404);
        }

        // Определяем окружение - если URL не заканчивается на "dev", то это prod
        $botEnvironment = 'prod';
        
        // Логируем информацию о webhook
        \Log::info("Bot: Processing webhook for bot: {$botName}", [
            'bot_environment' => $botEnvironment,
            'webhook_url' => $botModel->getWebhookUrlForEnvironment($botEnvironment),
            'full_webhook_url' => $botModel->getFullWebhookUrlForEnvironment($botEnvironment)
        ]);

        // Проверяем, что у бота есть токен для определенного окружения
        if (!$botModel->hasTokenForEnvironment($botEnvironment)) {
            \Log::error("Bot: No token for environment '{$botEnvironment}' for bot: {$botName}");
            return response()->json(['error' => 'Bot not configured for this environment'], 403);
        }

        // Устанавливаем окружение для бота
        Bot::setCurrentEnvironment($botEnvironment);

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
})->name('bot.webhook.prod');

/**
 * Роут для dev окружения с суффиксом "dev"
 * URL: /webhook/{botName}dev
 */
Route::post('/webhook/{botName}dev', function ($botName) {
    try {
        // Ищем бота в базе данных
        $botModel = Bot::byName($botName)->where('enabled', true)->first();
        
        if (!$botModel) {
            \Log::error("Bot: Bot not found or disabled: {$botName}");
            return response()->json(['error' => 'Bot not found or disabled'], 404);
        }

        // Определяем окружение - если URL заканчивается на "dev", то это dev
        $botEnvironment = 'dev';
        
        // Логируем информацию о webhook
        \Log::info("Bot: Processing dev webhook for bot: {$botName}", [
            'bot_environment' => $botEnvironment,
            'webhook_url' => $botModel->getWebhookUrlForEnvironment($botEnvironment),
            'full_webhook_url' => $botModel->getFullWebhookUrlForEnvironment($botEnvironment)
        ]);

        // Проверяем, что у бота есть токен для определенного окружения
        if (!$botModel->hasTokenForEnvironment($botEnvironment)) {
            \Log::error("Bot: No token for environment '{$botEnvironment}' for bot: {$botName}");
            return response()->json(['error' => 'Bot not configured for this environment'], 403);
        }

        // Устанавливаем окружение для бота
        Bot::setCurrentEnvironment($botEnvironment);

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
        \Log::error("Bot: Error processing dev webhook for {$botName}: " . $e->getMessage(), [
            'bot' => $botName,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json(['error' => 'Internal server error'], 500);
    }
})->name('bot.webhook.dev'); 