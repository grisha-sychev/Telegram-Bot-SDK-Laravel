<?php

namespace Bot\Modules;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

trait I18nModule
{
    protected array $translations = [];
    protected string $locale = 'en';
    protected string $fallbackLocale = 'en';
    protected string $translationsPath;

    /**
     * Initialize i18n module
     */
    public function i18nModule() 
    {
        $this->translationsPath = resource_path('lang');
        $this->loadTranslations();
    }

    /**
     * Load translations from language files
     */
    protected function loadTranslations(): void
    {
        $this->loadLocaleTranslations($this->locale);
        
        if ($this->locale !== $this->fallbackLocale) {
            $this->loadLocaleTranslations($this->fallbackLocale);
        }
    }

    /**
     * Load translations for specific locale
     */
    protected function loadLocaleTranslations(string $locale): void
    {
        $langPath = $this->translationsPath . '/' . $locale;
        
        if (!File::exists($langPath)) {
            return;
        }

        $files = File::files($langPath);
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $key = $file->getFilenameWithoutExtension();
                $this->translations[$locale][$key] = require $file->getPathname();
            }
        }
    }

    /**
     * Set locale
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        $this->loadTranslations();
        return $this;
    }

    /**
     * Get current locale
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Translate text using loaded translations
     */
    public function translate(string $key, array $parameters = []): string
    {
        $cacheKey = 'i18n_' . md5($key . $this->locale . json_encode($parameters));
        
        return Cache::rememberForever($cacheKey, function () use ($key, $parameters) {
            $translation = $this->getTranslation($key);
            
            if ($translation === $key) {
                return $key; // Return original key if translation not found
            }
            
            return $this->replaceParameters($translation, $parameters);
        });
    }

    /**
     * Get translation for key
     */
    protected function getTranslation(string $key): string
    {
        // Try current locale first
        $translation = $this->getTranslationFromLocale($key, $this->locale);
        
        if ($translation !== null) {
            return $translation;
        }
        
        // Try fallback locale
        if ($this->locale !== $this->fallbackLocale) {
            $translation = $this->getTranslationFromLocale($key, $this->fallbackLocale);
            if ($translation !== null) {
                return $translation;
            }
        }
        
        return $key;
    }

    /**
     * Get translation from specific locale
     */
    protected function getTranslationFromLocale(string $key, string $locale): ?string
    {
        if (!isset($this->translations[$locale])) {
            return null;
        }

        $keys = explode('.', $key);
        $current = $this->translations[$locale];

        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                return null;
            }
            $current = $current[$k];
        }

        return is_string($current) ? $current : null;
    }

    /**
     * Replace parameters in translation
     */
    protected function replaceParameters(string $text, array $parameters): string
    {
        foreach ($parameters as $key => $value) {
            $text = str_replace(':' . $key, $value, $text);
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        
        return $text;
    }

    /**
     * Translate array of texts
     */
    public function translateArray(array $input): array
    {
        $cacheKey = 'i18n_array_' . md5(json_encode($input) . $this->locale);
        
        return Cache::rememberForever($cacheKey, function () use ($input) {
            return array_map(function ($item) {
                if (is_array($item)) {
                    if (count($item) === 1) {
                        $item[0] = $this->translate($item[0]);
                    } 
                    if (count($item) === 2) {
                        $item[1] = $this->translate($item[1]);
                    } 
                } elseif (is_string($item)) {
                    $item = $this->translate($item);
                }
                return $item;
            }, $input);
        });
    }

    /**
     * Alias for translate method (compatibility with old TranslateModule)
     */
    public function trans($input)
    {
        if (is_array($input)) {
            return $this->translateArray($input);
        } elseif (is_string($input)) {
            return $this->translate($input);
        }
        
        return $input;
    }

    /**
     * Check if translation exists
     */
    public function hasTranslation(string $key): bool
    {
        return $this->getTranslation($key) !== $key;
    }

    /**
     * Get all available locales
     */
    public function getAvailableLocales(): array
    {
        if (!File::exists($this->translationsPath)) {
            return [$this->fallbackLocale];
        }

        $locales = [];
        $directories = File::directories($this->translationsPath);
        
        foreach ($directories as $directory) {
            $locales[] = basename($directory);
        }
        
        return array_unique(array_merge($locales, [$this->fallbackLocale]));
    }

    /**
     * Add custom translation
     */
    public function addTranslation(string $key, string $value, string $locale = null): self
    {
        $locale = $locale ?: $this->locale;
        
        if (!isset($this->translations[$locale])) {
            $this->translations[$locale] = [];
        }
        
        $keys = explode('.', $key);
        $current = &$this->translations[$locale];
        
        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }
        
        $current = $value;
        
        return $this;
    }
} 