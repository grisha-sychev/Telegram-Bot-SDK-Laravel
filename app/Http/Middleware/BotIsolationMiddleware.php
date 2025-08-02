<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Bot;
use Illuminate\Support\Facades\Log;

class BotIsolationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $botName = $request->route('botName');
        
        if (!$botName) {
            return response()->json(['error' => 'Bot name not provided'], 400);
        }

        // Ищем бота в базе данных
        $botModel = Bot::byName($botName)->where('enabled', true)->first();
        
        if (!$botModel) {
            Log::warning("BotIsolation: Bot not found or disabled: {$botName}");
            return response()->json(['error' => 'Bot not found or disabled'], 404);
        }

        // Определяем окружение на основе домена запроса
        $requestDomain = $request->getHost();
        $botEnvironment = $this->determineBotEnvironment($botModel, $requestDomain);
        
        if (!$botEnvironment) {
            Log::error("BotIsolation: Domain mismatch for bot: {$botName}", [
                'request_domain' => $requestDomain,
                'dev_domain' => $botModel->dev_domain,
                'prod_domain' => $botModel->prod_domain
            ]);
            return response()->json(['error' => 'Domain not configured for this environment'], 403);
        }

        // Проверяем, что у бота есть токен для определенного окружения
        if (!$botModel->hasTokenForEnvironment($botEnvironment)) {
            Log::error("BotIsolation: No token for environment '{$botEnvironment}' for bot: {$botName}");
            return response()->json(['error' => 'Bot not configured for this environment'], 403);
        }

        // Проверяем изоляцию webhook URL
        if (!$this->checkWebhookIsolation($botModel, $botEnvironment)) {
            Log::error("BotIsolation: Webhook isolation check failed for bot: {$botName}", [
                'environment' => $botEnvironment,
                'webhook_url' => $botModel->webhook_url
            ]);
            return response()->json(['error' => 'Webhook isolation check failed'], 403);
        }

        // Проверяем, что нет конфликтующих ботов с тем же webhook URL в другом окружении
        if (!$this->checkCrossEnvironmentIsolation($botModel, $botEnvironment)) {
            Log::error("BotIsolation: Cross-environment isolation check failed for bot: {$botName}", [
                'environment' => $botEnvironment,
                'webhook_url' => $botModel->webhook_url
            ]);
            return response()->json(['error' => 'Cross-environment isolation check failed'], 403);
        }

        // Устанавливаем окружение для бота
        Bot::setCurrentEnvironment($botEnvironment);
        
        // Добавляем информацию о боте в request для использования в контроллере
        $request->attributes->set('bot_model', $botModel);
        $request->attributes->set('bot_environment', $botEnvironment);

        Log::info("BotIsolation: Successfully isolated bot: {$botName}", [
            'environment' => $botEnvironment,
            'domain' => $botModel->getDomainForEnvironment($botEnvironment),
            'request_domain' => $requestDomain
        ]);

        return $next($request);
    }

    /**
     * Определить окружение бота на основе домена запроса и токенов
     */
    private function determineBotEnvironment(Bot $bot, string $requestDomain): ?string
    {
        // Проверяем, соответствует ли домен запроса доменам бота
        $devHost = parse_url($bot->dev_domain, PHP_URL_HOST);
        $prodHost = parse_url($bot->prod_domain, PHP_URL_HOST);
        
        // Если домен не соответствует ни одному из доменов бота
        if ($requestDomain !== $devHost && $requestDomain !== $prodHost) {
            return null;
        }
        
        // Если домены разные - определяем по домену
        if ($devHost !== $prodHost) {
            if ($requestDomain === $devHost) {
                return 'dev';
            }
            if ($requestDomain === $prodHost) {
                return 'prod';
            }
        }
        
        // Если домены одинаковые - определяем по токену в запросе
        if ($devHost === $prodHost && $requestDomain === $devHost) {
            // Извлекаем токен из запроса
            $requestBody = request()->getContent();
            $requestData = json_decode($requestBody, true);
            $botToken = $this->extractBotToken($requestData);
            
            if ($botToken) {
                // Сравниваем с токенами бота
                if ($bot->dev_token && $botToken === $bot->dev_token) {
                    Log::info("BotIsolation: Determined dev environment by token for bot: {$bot->name}");
                    return 'dev';
                }
                
                if ($bot->prod_token && $botToken === $bot->prod_token) {
                    Log::info("BotIsolation: Determined prod environment by token for bot: {$bot->name}");
                    return 'prod';
                }
                
                // Если токен не совпадает ни с одним из токенов бота
                Log::warning("BotIsolation: Unknown token for bot: {$bot->name}", [
                    'received_token' => substr($botToken, 0, 10) . '...',
                    'dev_token' => $bot->dev_token ? substr($bot->dev_token, 0, 10) . '...' : 'null',
                    'prod_token' => $bot->prod_token ? substr($bot->prod_token, 0, 10) . '...' : 'null'
                ]);
                return null;
            }
            
            // Если токен не найден в запросе, но у бота есть оба токена
            if ($bot->dev_token && $bot->prod_token) {
                Log::warning("BotIsolation: No token in request for bot with both tokens: {$bot->name}");
                return null;
            }
            
            // Если у бота только один токен, используем его окружение
            if ($bot->dev_token && !$bot->prod_token) {
                Log::info("BotIsolation: Using dev environment (only dev token available) for bot: {$bot->name}");
                return 'dev';
            }
            
            if ($bot->prod_token && !$bot->dev_token) {
                Log::info("BotIsolation: Using prod environment (only prod token available) for bot: {$bot->name}");
                return 'prod';
            }
        }
        
        return null;
    }

    /**
     * Проверить изоляцию webhook URL
     */
    private function checkWebhookIsolation(Bot $bot, string $environment): bool
    {
        if (!$bot->webhook_url) {
            return false;
        }

        // Получаем полный webhook URL для текущего окружения
        $currentWebhookUrl = $bot->getFullWebhookUrlForEnvironment($environment);
        
        if (!$currentWebhookUrl) {
            return false;
        }

        // Проверяем, что webhook URL соответствует текущему окружению
        $requestUrl = request()->url();
        $webhookPath = parse_url($currentWebhookUrl, PHP_URL_PATH);
        $requestPath = parse_url($requestUrl, PHP_URL_PATH);

        // Проверяем, что путь webhook соответствует запросу
        if ($webhookPath && $requestPath && $webhookPath !== $requestPath) {
            return false;
        }

        return true;
    }

    /**
     * Проверить изоляцию между окружениями для предотвращения конфликтов
     */
    private function checkCrossEnvironmentIsolation(Bot $bot, string $currentEnvironment): bool
    {
        if (!$bot->webhook_url) {
            return true; // Нет webhook URL - нет конфликтов
        }

        // Ищем другие боты с тем же webhook URL
        $conflictingBots = Bot::enabled()
            ->where('id', '!=', $bot->id)
            ->where('webhook_url', $bot->webhook_url)
            ->get();

        foreach ($conflictingBots as $conflictingBot) {
            // Проверяем, что конфликтующий бот не активен в другом окружении
            $otherEnvironment = $currentEnvironment === 'dev' ? 'prod' : 'dev';
            
            // Если у конфликтующего бота есть токен и домен для другого окружения
            if ($conflictingBot->hasTokenForEnvironment($otherEnvironment) && 
                $conflictingBot->hasDomainForEnvironment($otherEnvironment)) {
                
                // Проверяем, что домены действительно разные
                $currentDomain = $bot->getDomainForEnvironment($currentEnvironment);
                $otherDomain = $conflictingBot->getDomainForEnvironment($otherEnvironment);
                
                if ($currentDomain && $otherDomain && $currentDomain !== $otherDomain) {
                    // Это нормально - боты изолированы по доменам
                    continue;
                }
                
                // Если домены одинаковые или один из них пустой, это конфликт
                Log::warning("BotIsolation: Cross-environment conflict detected", [
                    'current_bot' => $bot->name,
                    'current_environment' => $currentEnvironment,
                    'current_domain' => $currentDomain,
                    'conflicting_bot' => $conflictingBot->name,
                    'other_environment' => $otherEnvironment,
                    'other_domain' => $otherDomain,
                    'webhook_url' => $bot->webhook_url
                ]);
                
                return false;
            }
        }

        return true;
    }
    
    /**
     * Извлечь токен бота из запроса
     */
    private function extractBotToken(?array $requestData): ?string
    {
        // Проверяем заголовки
        $authorization = request()->header('Authorization');
        if ($authorization && strpos($authorization, 'Bearer ') === 0) {
            return substr($authorization, 7);
        }
        
        // Проверяем тело запроса
        if ($requestData) {
            // Telegram webhook может содержать токен в разных местах
            if (isset($requestData['bot_token'])) {
                return $requestData['bot_token'];
            }
            
            if (isset($requestData['token'])) {
                return $requestData['token'];
            }
            
            // Проверяем в message/chat объектах
            if (isset($requestData['message']['chat']['id'])) {
                // Можно добавить дополнительную логику для определения токена
                // на основе chat_id или других параметров
            }
        }
        
        // Проверяем параметры URL
        $token = request()->query('token');
        if ($token) {
            return $token;
        }
        
        return null;
    }
} 