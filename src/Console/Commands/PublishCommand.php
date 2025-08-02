<?php

namespace Bot\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:publish {--force : Принудительно обновить существующие файлы} {--tag=* : Теги для публикации}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Публикация файлов пакета с возможностью обновления';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        $tags = $this->option('tag');
        
        $this->info('📦 Публикация файлов пакета Telegram Bot SDK...');
        
        if ($force) {
            $this->warn('⚠️  Режим принудительного обновления включен');
        }
        
        // Определяем теги для публикации
        if (empty($tags)) {
            $tags = ['bot']; // Публикуем все файлы по умолчанию
        }
        
        $this->info('🏷️  Теги для публикации: ' . implode(', ', $tags));
        
        // Публикуем файлы
        foreach ($tags as $tag) {
            if ($force) {
                $this->publishWithForce($tag);
            } else {
                $this->publishTag($tag, $force);
            }
        }
        
        $this->info('✅ Публикация завершена!');
        
        return 0;
    }
    
    /**
     * Публикация файлов по тегу
     */
    private function publishTag(string $tag, bool $force): void
    {
        $this->line("📤 Публикация тега: {$tag}");
        
        try {
            // Выполняем стандартную публикацию
            $result = Artisan::call('vendor:publish', [
                '--provider' => 'Bot\Providers\BotServiceProvider',
                '--tag' => $tag,
                '--force' => $force
            ]);
            
            if ($result === 0) {
                $this->info("✅ Тег '{$tag}' опубликован успешно");
            } else {
                $this->error("❌ Ошибка публикации тега '{$tag}'");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Ошибка при публикации тега '{$tag}': " . $e->getMessage());
        }
    }
    
    /**
     * Публикация файлов с принудительным обновлением
     */
    private function publishWithForce(string $tag): void
    {
        $this->line("📤 Принудительная публикация тега: {$tag}");
        
        // Определяем пути для публикации
        $paths = $this->getPathsForTag($tag);
        
        foreach ($paths as $source => $destination) {
            $this->copyFile($source, $destination, true);
        }
    }
    
    /**
     * Получить пути для публикации по тегу
     */
    private function getPathsForTag(string $tag): array
    {
        $paths = [];
        
        // Базовый путь к пакету
        $packagePath = __DIR__ . '/../../..';
        
        switch ($tag) {
            case 'bot-config':
            case 'config':
                $paths[$packagePath . '/config/bot.php'] = config_path('bot.php');
                break;
                
            case 'bot-app':
            case 'app':
                $paths[$packagePath . '/app'] = app_path();
                break;
                
            case 'bot-routes':
            case 'routes':
                $paths[$packagePath . '/routes'] = base_path('routes');
                break;
                
            case 'bot-database':
            case 'database':
            case 'migrations':
                $paths[$packagePath . '/database'] = database_path();
                break;
                
            case 'bot-lang':
            case 'lang':
                $paths[$packagePath . '/resources/lang'] = base_path('resources/lang');
                break;
                
            case 'bot':
            default:
                $paths = [
                    $packagePath . '/app' => app_path(),
                    $packagePath . '/config' => config_path(),
                    $packagePath . '/database' => database_path(),
                    $packagePath . '/routes' => base_path('routes'),
                    $packagePath . '/resources' => base_path('resources'),
                ];
                break;
        }
        
        return $paths;
    }
    
    /**
     * Копирование файла с проверкой
     */
    private function copyFile(string $source, string $destination, bool $force = false): void
    {
        if (!file_exists($source)) {
            $this->warn("⚠️  Источник не найден: {$source}");
            return;
        }
        
        if (is_dir($source)) {
            $this->copyDirectory($source, $destination, $force);
        } else {
            $this->copySingleFile($source, $destination, $force);
        }
    }
    
    /**
     * Копирование директории
     */
    private function copyDirectory(string $source, string $destination, bool $force): void
    {
        if (!is_dir($source)) {
            return;
        }
        
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($source) + 1);
            $destPath = $destination . '/' . $relativePath;
            
            if ($file->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                $this->copySingleFile($filePath, $destPath, $force);
            }
        }
    }
    
    /**
     * Копирование одного файла
     */
    private function copySingleFile(string $source, string $destination, bool $force): void
    {
        $destDir = dirname($destination);
        
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        
        if (file_exists($destination) && !$force) {
            $this->line("⏭️  Пропущен (существует): " . basename($destination));
            return;
        }
        
        if (copy($source, $destination)) {
            $this->line("✅ Скопирован: " . basename($destination));
        } else {
            $this->error("❌ Ошибка копирования: " . basename($destination));
        }
    }
} 