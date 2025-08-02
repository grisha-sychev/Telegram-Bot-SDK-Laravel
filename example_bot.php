<?php

require_once __DIR__ . '/vendor/autoload.php';

use Bot\LightBot;
use Bot\Modules\I18nModule;

class ExampleBot extends LightBot
{
    use I18nModule;
    public function start()
    {
        // Автоматический перевод в sendSelf
        $keyboard = [
            [$this->translate('messages.button.start')],
            [$this->translate('messages.button.help')],
            [$this->translate('messages.button.settings')]
        ];
        
        $this->sendSelf($this->translate('messages.welcome'), $keyboard);
    }
    
    public function help()
    {
        $helpText = $this->translate('messages.help');
        $this->sendSelf($helpText);
    }
    
    public function settings()
    {
        $settingsText = $this->translate('messages.settings');
        $backButton = [[$this->translate('messages.button.back')]];
        $this->sendSelf($settingsText, $backButton);
    }
    
    public function greeting()
    {
        // Пример с параметрами
        $username = $this->getUsername() ?: 'User';
        $greeting = $this->translate('messages.user.greeting', ['name' => $username]);
        $this->sendSelf($greeting);
    }
    
    public function changeLanguage()
    {
        $currentLang = $this->getUserLanguage();
        $newLang = $currentLang === 'en' ? 'ru' : 'en';
        
        $this->setLanguage($newLang);
        $message = $this->translate('messages.user.language_changed', ['language' => $newLang]);
        $this->sendSelf($message);
    }
    
    public function demoParameters()
    {
        // Демонстрация работы с параметрами
        $message = $this->translate('messages.user.greeting', [
            'name' => 'John',
            'bot_name' => 'ExampleBot'
        ]);
        $this->sendSelf($message);
    }
}

// Запуск бота
$bot = new ExampleBot();
$bot->run(); 