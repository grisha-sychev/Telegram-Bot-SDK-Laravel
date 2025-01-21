<?php

namespace Teg\Modules;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Teg\Support\Facades\Services;
use Illuminate\Support\Facades\Cache;

trait TranslateModule
{
    public function translateModule() {}

    public function translate(string $text)
    {
        $lang_code = $this->getFrom->getLanguageCode();

            $preserve = '/\{\{([^}]+)\}\}/';

            $tr = new GoogleTranslate();
            $tr->setTarget($lang_code);
            $tr->setOptions([
                'verify' => Services::isSSLAvailable()
            ]);
    
            $transText = $tr->preserveParameters($preserve)->translate($text);
            $transText = preg_replace($preserve, '$1', $transText);
    
            return $transText;
    }
    
    public function trans($input)
    {
        if (is_array($input)) {
            $cacheKey = 'tr_' . md5(json_encode($input) . $this->getMessageText);
        
            return Cache::rememberForever($cacheKey, function () use ($input) {
                return array_map(function ($item) {
                    if (is_array($item)) {
                        if(count($item) === 1) {
                            $item[0] = $this->translate($item[0]);
                        } 
                        if(count($item) === 2) {
                            $item[1] = $this->translate($item[1]);
                        } 
                    }
                    return $item;
                }, $input);
            });
        } elseif (is_string($input)) {
            return $this->translate($input);
        }
    }
    
}
