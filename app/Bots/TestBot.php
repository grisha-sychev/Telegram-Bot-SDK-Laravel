<?php

namespace App\Bots;

use Bot\LightBot;
use App\Models\Bot;

class TestBot extends LightBot
{
    public function main()
    {
        $currentEnvironment = Bot::getCurrentEnvironment();
        $botName = $this->bot ?? 'unknown';
        
        // Логируем информацию о боте
        \Log::info("TestBot: Bot info", [
            'bot_name' => $botName,
            'environment' => $currentEnvironment,
            'token' => $this->token ? substr($this->token, 0, 10) . '...' : 'not set'
        ]);
        
        // Отправляем сообщение с информацией об окружении
        $message = "🤖 Тестовый бот: {$botName}\n";
        $message .= "🌍 Окружение: {$currentEnvironment}\n";
        $message .= "🔑 Токен: " . ($this->token ? substr($this->token, 0, 10) . '...' : 'не установлен');
        
        $this->sendSelf($message);
        
        return response()->json(['status' => 'success']);
    }
} 