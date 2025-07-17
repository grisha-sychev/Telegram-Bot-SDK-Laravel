<?php

use Illuminate\Support\Facades\Route;
use App\Models\Bot;

/*
|--------------------------------------------------------------------------
| TegBot Webhook Routes  
|--------------------------------------------------------------------------
|
| Роуты для обработки webhook'ов от Telegram. Поддерживает как классический
| подход /bot/{token}, так и современную мультиботную архитектуру.
|
*/

/**
 * Основной webhook роут (обратная совместимость)
 * URL: /bot/{token}
 * Поддерживает как старую, так и новую мультиботную архитектуру
 */
Route::post('/bot/{token}', function ($token) {
    $config = config('tegbot');
    $botName = null;
    $botConfig = null;

    // Сначала проверяем новую мультиботную структуру
    if (isset($config['bots']) && is_array($config['bots'])) {
        foreach ($config['bots'] as $name => $settings) {
            if (($settings['token'] ?? '') === $token) {
                $botName = $name;
                $botConfig = $settings;
                break;
            }
        }
    }

    // Fallback на старую структуру для обратной совместимости
    if (!$botName) {
        $botName = array_search($token, $config);
        if ($botName !== false) {
            $botConfig = ['token' => $token, 'enabled' => true];
        }
    }

    if (!$botName) {
        \Log::warning("TegBot: Unknown token requested: " . substr($token, 0, 10) . "...");
        return response()->json(['error' => 'Bot not found'], 404);
    }

    // Проверяем что бот активен (только для новой структуры)
    if (isset($botConfig['enabled']) && !$botConfig['enabled']) {
        \Log::info("TegBot: Disabled bot accessed: {$botName}");
        return response()->json(['error' => 'Bot disabled'], 403);
    }

    // Формируем имя класса бота
    $class = 'App\\Bots\\' . ucfirst($botName) . "Bot";

    if (!class_exists($class)) {
        \Log::error("TegBot: Bot class not found: {$class}");
        return response()->json(['error' => 'Bot class not found'], 404);
    }
    
    try {
        // Создаем экземпляр бота
        $bot = new $class();
        
        // Устанавливаем конфигурацию если есть и метод существует
        if ($botConfig && method_exists($bot, 'setConfig')) {
            $bot->setConfig($botConfig);
        }
        
        // Устанавливаем токен для LightBot
        if (method_exists($bot, 'setToken')) {
            $bot->setToken($token);
        }
        
        // Запускаем обработку
        return $bot->run()->main();
        
    } catch (\Exception $e) {
        \Log::error("TegBot: Error processing webhook for {$botName}: " . $e->getMessage(), [
            'bot' => $botName,
            'token_prefix' => substr($token, 0, 10),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json(['error' => 'Internal server error'], 500);
    }
})->name('tegbot.webhook');

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
            \Log::warning("TegBot: Bot not found or disabled: {$botName}");
            return response()->json(['error' => 'Bot not found or disabled'], 404);
        }

        // Формируем имя класса бота
        $class = $botModel->getBotClass();

        if (!class_exists($class)) {
            \Log::error("TegBot: Bot class not found: {$class}");
            return response()->json(['error' => 'Bot class not found'], 404);
        }
        
        // Создаем экземпляр бота
        $bot = new $class();
        
        // Устанавливаем токен для LightBot
        if (method_exists($bot, 'setToken')) {
            $bot->setToken($botModel->token);
        }
        
        // Устанавливаем дополнительные настройки если метод существует
        if (method_exists($bot, 'setBotModel')) {
            $bot->setBotModel($botModel);
        }
        
        \Log::info("TegBot: Processing webhook for bot: {$botName}");
        
        // Запускаем обработку
        if (method_exists($bot, 'run')) {
            return $bot->run()->main();
        } elseif (method_exists($bot, 'main')) {
            return $bot->main();
        } else {
            \Log::error("TegBot: Bot {$class} has no main() or run() method");
            return response()->json(['error' => 'Bot method not found'], 500);
        }
        
    } catch (\Exception $e) {
        \Log::error("TegBot: Error processing webhook for {$botName}: " . $e->getMessage(), [
            'bot' => $botName,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json(['error' => 'Internal server error'], 500);
    }
})->name('tegbot.webhook.modern');
