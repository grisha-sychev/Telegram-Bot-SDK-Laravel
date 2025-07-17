# 📁 Медиа обработка TegBot v2.0

## Обзор системы медиа

TegBot v2.0 предоставляет продвинутую систему медиа обработки для мультиботных экосистем:

- 🎯 **Multi-Bot Media Management**: Изолированное управление медиа для каждого бота
- 📁 **Advanced File Handling**: Комплексная работа с файлами и документами
- 🖼️ **Image Processing**: Обработка изображений с ресайзингом и оптимизацией
- 🎬 **Video & Audio Support**: Полная поддержка видео и аудио контента
- ☁️ **Cloud Storage Integration**: Интеграция с внешними хранилищами
- 🔐 **Secure Media Handling**: Безопасная обработка с валидацией и сканированием
- 📊 **Media Analytics**: Аналитика использования медиа контента

> ⚠️ **Важно**: v2.0 полностью переработал систему медиа для работы с мультиботными экосистемами.

## 🎯 Конфигурация медиа по ботам

### Настройка хранилища для каждого бота

```bash
# Настройка хранилища для конкретного бота
php artisan teg:bot config shop_bot --set media.storage_disk=shop_media
php artisan teg:bot config shop_bot --set media.max_file_size=50MB
php artisan teg:bot config shop_bot --set media.allowed_types="image,video,document"

# Настройка обработки изображений
php artisan teg:bot config shop_bot --set media.image.auto_resize=true
php artisan teg:bot config shop_bot --set media.image.max_width=1920
php artisan teg:bot config shop_bot --set media.image.quality=85

# Настройка видео
php artisan teg:bot config shop_bot --set media.video.auto_convert=true
php artisan teg:bot config shop_bot --set media.video.max_duration=300
php artisan teg:bot config shop_bot --set media.video.output_format=mp4

# Проверка настроек медиа
php artisan teg:bot config shop_bot --show media
```

### Изолированные хранилища

```php
// config/filesystems.php
'disks' => [
    // Общее хранилище для системы
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
    
    // Хранилище для shop_bot
    'shop_media' => [
        'driver' => 'local',
        'root' => storage_path('app/bots/shop/media'),
        'url' => env('APP_URL').'/storage/bots/shop',
        'visibility' => 'public',
    ],
    
    // Хранилище для support_bot
    'support_media' => [
        'driver' => 'local',
        'root' => storage_path('app/bots/support/media'),
        'url' => env('APP_URL').'/storage/bots/support',
        'visibility' => 'private',
    ],
    
    // Облачное хранилище для analytics_bot
    'analytics_media' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET_ANALYTICS'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
    ],
],
```

## 📁 Обработка файлов в ботах

### Базовая работа с медиа

```php
<?php
// app/Bots/MediaBot.php
namespace App\Bots;

use Teg\LightBot;
use App\TegBot\Media\MediaHandler;

class MediaBot extends LightBot
{
    protected MediaHandler $mediaHandler;
    
    public function __construct()
    {
        parent::__construct();
        $this->mediaHandler = new MediaHandler($this->getBotName());
    }
    
    public function main(): void
    {
        $this->commands();
        
        if ($this->hasPhoto()) {
            $this->handlePhoto();
        } elseif ($this->hasVideo()) {
            $this->handleVideo();
        } elseif ($this->hasDocument()) {
            $this->handleDocument();
        } elseif ($this->hasAudio()) {
            $this->handleAudio();
        } elseif ($this->hasVoice()) {
            $this->handleVoice();
        } else {
            $this->fallback();
        }
    }
    
    public function commands(): void
    {
        $this->registerCommand('upload', function () {
            $this->requestFileUpload();
        }, ['description' => 'Загрузить файл']);
        
        $this->registerCommand('gallery', function () {
            $this->showGallery();
        }, ['description' => 'Показать галерею']);
        
        $this->registerCommand('files', function () {
            $this->showFiles();
        }, ['description' => 'Список файлов']);
        
        $this->registerCommand('stats', function () {
            $this->showMediaStats();
        }, ['description' => 'Статистика медиа']);
    }
    
    private function handlePhoto(): void
    {
        $photo = $this->getPhoto();
        
        try {
            $mediaFile = $this->mediaHandler->processPhoto($photo, [
                'user_id' => $this->getUserId,
                'chat_id' => $this->getChatId,
                'caption' => $this->getCaption(),
                'auto_resize' => true,
                'generate_thumbnails' => true
            ]);
            
            $this->sendMessage($this->getChatId, 
                "✅ Фото обработано и сохранено!\n\n" .
                "📁 **Файл:** {$mediaFile->filename}\n" .
                "📐 **Размер:** {$mediaFile->width}x{$mediaFile->height}\n" .
                "💾 **Объем:** " . $this->formatFileSize($mediaFile->size) . "\n" .
                "🔗 **ID:** `{$mediaFile->id}`",
                ['parse_mode' => 'Markdown']
            );
            
        } catch (\Exception $e) {
            $this->sendMessage($this->getChatId, 
                "❌ Ошибка обработки фото: " . $e->getMessage()
            );
        }
    }
    
    private function handleVideo(): void
    {
        $video = $this->getVideo();
        
        if (!$this->mediaHandler->validateVideo($video)) {
            $this->sendMessage($this->getChatId, 
                "❌ Видео не соответствует требованиям:\n" .
                "• Максимальный размер: " . $this->mediaHandler->getMaxFileSize() . "\n" .
                "• Максимальная длительность: " . $this->mediaHandler->getMaxDuration() . " сек\n" .
                "• Поддерживаемые форматы: mp4, avi, mov"
            );
            return;
        }
        
        $this->sendMessage($this->getChatId, 
            "🎬 Обрабатываю видео... Это может занять несколько минут."
        );
        
        try {
            $mediaFile = $this->mediaHandler->processVideo($video, [
                'user_id' => $this->getUserId,
                'convert_to_mp4' => true,
                'generate_thumbnail' => true,
                'compress' => true
            ]);
            
            $this->sendMessage($this->getChatId, 
                "✅ Видео обработано!\n\n" .
                "📁 **Файл:** {$mediaFile->filename}\n" .
                "⏱️ **Длительность:** " . $this->formatDuration($mediaFile->duration) . "\n" .
                "📐 **Разрешение:** {$mediaFile->width}x{$mediaFile->height}\n" .
                "💾 **Размер:** " . $this->formatFileSize($mediaFile->size),
                ['parse_mode' => 'Markdown']
            );
            
        } catch (\Exception $e) {
            $this->sendMessage($this->getChatId, 
                "❌ Ошибка обработки видео: " . $e->getMessage()
            );
        }
    }
    
    private function handleDocument(): void
    {
        $document = $this->getDocument();
        
        // Проверяем тип документа
        $fileType = $this->mediaHandler->detectFileType($document);
        
        if (!$this->mediaHandler->isAllowedFileType($fileType)) {
            $allowedTypes = implode(', ', $this->mediaHandler->getAllowedTypes());
            $this->sendMessage($this->getChatId, 
                "❌ Тип файла не поддерживается.\n" .
                "Разрешенные типы: {$allowedTypes}"
            );
            return;
        }
        
        try {
            $mediaFile = $this->mediaHandler->processDocument($document, [
                'user_id' => $this->getUserId,
                'scan_for_viruses' => true,
                'extract_text' => in_array($fileType, ['pdf', 'docx', 'txt']),
                'generate_preview' => in_array($fileType, ['pdf', 'docx'])
            ]);
            
            $message = "📄 Документ сохранен!\n\n";
            $message .= "📁 **Имя:** {$mediaFile->original_name}\n";
            $message .= "📋 **Тип:** {$mediaFile->mime_type}\n";
            $message .= "💾 **Размер:** " . $this->formatFileSize($mediaFile->size) . "\n";
            
            if ($mediaFile->has_text_content) {
                $message .= "📝 **Текст извлечен:** " . mb_substr($mediaFile->text_content, 0, 100) . "...\n";
            }
            
            $this->sendMessage($this->getChatId, $message, [
                'parse_mode' => 'Markdown'
            ]);
            
        } catch (\Exception $e) {
            $this->sendMessage($this->getChatId, 
                "❌ Ошибка обработки документа: " . $e->getMessage()
            );
        }
    }
}
```

## 🎨 Продвинутая обработка изображений

### MediaHandler с обработкой изображений

```php
<?php
// app/TegBot/Media/MediaHandler.php
namespace App\TegBot\Media;

use App\Models\Bot;
use App\Models\MediaFile;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class MediaHandler
{
    private Bot $bot;
    private array $config;
    private string $storageDisk;
    
    public function __construct(string $botName)
    {
        $this->bot = Bot::where('name', $botName)->firstOrFail();
        $this->config = $this->bot->getMediaConfig();
        $this->storageDisk = $this->config['storage_disk'] ?? 'public';
    }
    
    public function processPhoto(array $photo, array $options = []): MediaFile
    {
        // Получаем файл наибольшего размера
        $largestPhoto = collect($photo)->sortByDesc('file_size')->first();
        
        // Скачиваем файл от Telegram
        $telegramFile = $this->downloadFromTelegram($largestPhoto['file_id']);
        
        // Создаем запись в БД
        $mediaFile = MediaFile::create([
            'bot_name' => $this->bot->name,
            'user_id' => $options['user_id'],
            'chat_id' => $options['chat_id'],
            'telegram_file_id' => $largestPhoto['file_id'],
            'type' => 'image',
            'original_name' => 'photo_' . now()->format('Y-m-d_H-i-s') . '.jpg',
            'mime_type' => 'image/jpeg',
            'size' => $largestPhoto['file_size'],
            'width' => $largestPhoto['width'] ?? null,
            'height' => $largestPhoto['height'] ?? null,
            'caption' => $options['caption'] ?? null,
            'metadata' => []
        ]);
        
        // Сохраняем оригинал
        $originalPath = $this->generatePath($mediaFile, 'original');
        Storage::disk($this->storageDisk)->put($originalPath, $telegramFile);
        
        // Обрабатываем изображение
        $image = Image::make($telegramFile);
        $this->processImage($image, $mediaFile, $options);
        
        // Обновляем запись
        $mediaFile->update([
            'path' => $originalPath,
            'processed' => true,
            'processed_at' => now()
        ]);
        
        return $mediaFile;
    }
    
    private function processImage($image, MediaFile $mediaFile, array $options): void
    {
        $variants = [];
        
        // Автоматический ресайз
        if ($options['auto_resize'] ?? $this->config['image']['auto_resize'] ?? false) {
            $maxWidth = $this->config['image']['max_width'] ?? 1920;
            $maxHeight = $this->config['image']['max_height'] ?? 1080;
            
            if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
                $resized = clone $image;
                $resized->resize($maxWidth, $maxHeight, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                
                $resizedPath = $this->generatePath($mediaFile, 'resized');
                $resizedContent = $resized->encode('jpg', $this->config['image']['quality'] ?? 85);
                Storage::disk($this->storageDisk)->put($resizedPath, $resizedContent);
                
                $variants['resized'] = [
                    'path' => $resizedPath,
                    'width' => $resized->width(),
                    'height' => $resized->height(),
                    'size' => strlen($resizedContent)
                ];
            }
        }
        
        // Генерация миниатюр
        if ($options['generate_thumbnails'] ?? true) {
            $thumbnailSizes = $this->config['image']['thumbnail_sizes'] ?? [
                'small' => [150, 150],
                'medium' => [300, 300],
                'large' => [600, 600]
            ];
            
            foreach ($thumbnailSizes as $size => $dimensions) {
                $thumbnail = clone $image;
                $thumbnail->fit($dimensions[0], $dimensions[1]);
                
                $thumbnailPath = $this->generatePath($mediaFile, "thumb_{$size}");
                $thumbnailContent = $thumbnail->encode('jpg', 75);
                Storage::disk($this->storageDisk)->put($thumbnailPath, $thumbnailContent);
                
                $variants["thumbnail_{$size}"] = [
                    'path' => $thumbnailPath,
                    'width' => $thumbnail->width(),
                    'height' => $thumbnail->height(),
                    'size' => strlen($thumbnailContent)
                ];
            }
        }
        
        // Извлечение метаданных
        $metadata = $this->extractImageMetadata($image);
        
        // Обновляем запись с вариантами и метаданными
        $mediaFile->update([
            'variants' => $variants,
            'metadata' => $metadata
        ]);
    }
    
    public function processVideo(array $video, array $options = []): MediaFile
    {
        // Скачиваем видео от Telegram
        $telegramFile = $this->downloadFromTelegram($video['file_id']);
        
        // Создаем запись в БД
        $mediaFile = MediaFile::create([
            'bot_name' => $this->bot->name,
            'user_id' => $options['user_id'],
            'telegram_file_id' => $video['file_id'],
            'type' => 'video',
            'original_name' => $video['file_name'] ?? 'video_' . now()->format('Y-m-d_H-i-s') . '.mp4',
            'mime_type' => $video['mime_type'] ?? 'video/mp4',
            'size' => $video['file_size'],
            'width' => $video['width'] ?? null,
            'height' => $video['height'] ?? null,
            'duration' => $video['duration'] ?? null,
            'metadata' => []
        ]);
        
        // Сохраняем оригинал
        $originalPath = $this->generatePath($mediaFile, 'original');
        Storage::disk($this->storageDisk)->put($originalPath, $telegramFile);
        
        // Обрабатываем видео
        if ($options['convert_to_mp4'] ?? $this->config['video']['auto_convert'] ?? false) {
            $this->convertVideo($mediaFile, $originalPath, $options);
        }
        
        // Генерируем превью
        if ($options['generate_thumbnail'] ?? true) {
            $this->generateVideoThumbnail($mediaFile, $originalPath);
        }
        
        $mediaFile->update([
            'path' => $originalPath,
            'processed' => true,
            'processed_at' => now()
        ]);
        
        return $mediaFile;
    }
    
    private function convertVideo(MediaFile $mediaFile, string $originalPath, array $options): void
    {
        $outputPath = $this->generatePath($mediaFile, 'converted', 'mp4');
        $tempInput = storage_path('temp/' . uniqid() . '.tmp');
        $tempOutput = storage_path('temp/' . uniqid() . '.mp4');
        
        // Копируем файл во временную директорию
        file_put_contents($tempInput, Storage::disk($this->storageDisk)->get($originalPath));
        
        // FFmpeg команда для конвертации
        $ffmpegCommand = "ffmpeg -i {$tempInput} ";
        
        // Настройки качества
        if ($options['compress'] ?? false) {
            $ffmpegCommand .= "-crf 23 -preset medium ";
        }
        
        // Ограничение размера
        $maxWidth = $this->config['video']['max_width'] ?? 1280;
        $maxHeight = $this->config['video']['max_height'] ?? 720;
        $ffmpegCommand .= "-vf 'scale=min({$maxWidth}\\,iw):min({$maxHeight}\\,ih):force_original_aspect_ratio=decrease' ";
        
        $ffmpegCommand .= "-y {$tempOutput}";
        
        // Выполняем конвертацию
        exec($ffmpegCommand, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($tempOutput)) {
            // Сохраняем сконвертированное видео
            Storage::disk($this->storageDisk)->put($outputPath, file_get_contents($tempOutput));
            
            // Обновляем информацию о файле
            $convertedSize = filesize($tempOutput);
            $variants = $mediaFile->variants ?? [];
            $variants['converted'] = [
                'path' => $outputPath,
                'size' => $convertedSize,
                'format' => 'mp4'
            ];
            
            $mediaFile->update(['variants' => $variants]);
        }
        
        // Удаляем временные файлы
        @unlink($tempInput);
        @unlink($tempOutput);
    }
    
    private function generateVideoThumbnail(MediaFile $mediaFile, string $videoPath): void
    {
        $thumbnailPath = $this->generatePath($mediaFile, 'thumbnail', 'jpg');
        $tempVideo = storage_path('temp/' . uniqid() . '.tmp');
        $tempThumbnail = storage_path('temp/' . uniqid() . '.jpg');
        
        // Копируем видео во временную директорию
        file_put_contents($tempVideo, Storage::disk($this->storageDisk)->get($videoPath));
        
        // Генерируем миниатюру с помощью FFmpeg
        $ffmpegCommand = "ffmpeg -i {$tempVideo} -ss 00:00:01 -vframes 1 -y {$tempThumbnail}";
        exec($ffmpegCommand, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($tempThumbnail)) {
            // Оптимизируем миниатюру
            $image = Image::make($tempThumbnail);
            $image->fit(300, 300);
            
            $optimizedContent = $image->encode('jpg', 75);
            Storage::disk($this->storageDisk)->put($thumbnailPath, $optimizedContent);
            
            // Обновляем варианты
            $variants = $mediaFile->variants ?? [];
            $variants['thumbnail'] = [
                'path' => $thumbnailPath,
                'width' => $image->width(),
                'height' => $image->height(),
                'size' => strlen($optimizedContent)
            ];
            
            $mediaFile->update(['variants' => $variants]);
        }
        
        // Удаляем временные файлы
        @unlink($tempVideo);
        @unlink($tempThumbnail);
    }
}
```

## 🔐 Безопасность медиа

### Валидация и сканирование файлов

```php
<?php
// app/TegBot/Media/SecurityScanner.php
namespace App\TegBot\Media;

class MediaSecurityScanner
{
    private array $allowedMimeTypes = [
        'image' => [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp'
        ],
        'video' => [
            'video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo'
        ],
        'document' => [
            'application/pdf', 'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ],
        'audio' => [
            'audio/mpeg', 'audio/wav', 'audio/ogg'
        ]
    ];
    
    private array $dangerousExtensions = [
        'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'php', 'asp'
    ];
    
    public function validateFile(array $file, string $type): array
    {
        $validation = [
            'valid' => true,
            'errors' => [],
            'warnings' => []
        ];
        
        // Проверка размера файла
        $maxSize = config("tegbot.media.max_file_size.{$type}", 50 * 1024 * 1024); // 50MB
        if (($file['file_size'] ?? 0) > $maxSize) {
            $validation['valid'] = false;
            $validation['errors'][] = "Файл слишком большой. Максимум: " . $this->formatFileSize($maxSize);
        }
        
        // Проверка MIME типа
        $mimeType = $file['mime_type'] ?? '';
        $allowedTypes = $this->allowedMimeTypes[$type] ?? [];
        
        if (!in_array($mimeType, $allowedTypes)) {
            $validation['valid'] = false;
            $validation['errors'][] = "Неподдерживаемый тип файла: {$mimeType}";
        }
        
        // Проверка расширения файла
        $fileName = $file['file_name'] ?? '';
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (in_array($extension, $this->dangerousExtensions)) {
            $validation['valid'] = false;
            $validation['errors'][] = "Опасное расширение файла: {$extension}";
        }
        
        // Проверка специфичных параметров для типа файла
        switch ($type) {
            case 'image':
                $validation = $this->validateImage($file, $validation);
                break;
            case 'video':
                $validation = $this->validateVideo($file, $validation);
                break;
        }
        
        return $validation;
    }
    
    private function validateImage(array $file, array $validation): array
    {
        $maxWidth = config('tegbot.media.image.max_width', 4096);
        $maxHeight = config('tegbot.media.image.max_height', 4096);
        
        if (isset($file['width']) && $file['width'] > $maxWidth) {
            $validation['warnings'][] = "Изображение будет уменьшено до {$maxWidth}px по ширине";
        }
        
        if (isset($file['height']) && $file['height'] > $maxHeight) {
            $validation['warnings'][] = "Изображение будет уменьшено до {$maxHeight}px по высоте";
        }
        
        return $validation;
    }
    
    private function validateVideo(array $file, array $validation): array
    {
        $maxDuration = config('tegbot.media.video.max_duration', 300); // 5 минут
        
        if (isset($file['duration']) && $file['duration'] > $maxDuration) {
            $validation['valid'] = false;
            $validation['errors'][] = "Видео слишком длинное. Максимум: {$maxDuration} секунд";
        }
        
        return $validation;
    }
    
    public function scanForMalware(string $filePath): array
    {
        $result = [
            'clean' => true,
            'threats' => [],
            'scanner' => 'built-in'
        ];
        
        // Базовая проверка сигнатур
        $fileContent = file_get_contents($filePath);
        $suspiciousPatterns = [
            '/<%[\s]*php/i',           // PHP теги
            '/<%[\s]*script/i',        // Script теги  
            '/javascript:/i',          // JavaScript протокол
            '/vbscript:/i',            // VBScript протокол
            '/data:text\/html/i',      // Data URI с HTML
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $fileContent)) {
                $result['clean'] = false;
                $result['threats'][] = "Suspicious pattern detected: {$pattern}";
            }
        }
        
        // Интеграция с ClamAV (если доступен)
        if (function_exists('cl_scanfile')) {
            $clamResult = cl_scanfile($filePath);
            if ($clamResult !== CL_CLEAN) {
                $result['clean'] = false;
                $result['threats'][] = "ClamAV detected threat";
                $result['scanner'] = 'clamav';
            }
        }
        
        return $result;
    }
    
    public function extractMetadata(string $filePath, string $type): array
    {
        $metadata = [
            'file_size' => filesize($filePath),
            'created_at' => now(),
            'extracted_at' => now()
        ];
        
        switch ($type) {
            case 'image':
                $metadata = array_merge($metadata, $this->extractImageMetadata($filePath));
                break;
            case 'video':
                $metadata = array_merge($metadata, $this->extractVideoMetadata($filePath));
                break;
            case 'document':
                $metadata = array_merge($metadata, $this->extractDocumentMetadata($filePath));
                break;
        }
        
        return $metadata;
    }
    
    private function extractImageMetadata(string $filePath): array
    {
        $metadata = [];
        
        // EXIF данные
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($filePath);
            if ($exif) {
                $metadata['exif'] = [
                    'camera' => $exif['Model'] ?? null,
                    'datetime' => $exif['DateTime'] ?? null,
                    'gps' => isset($exif['GPSLatitude']) ? 'present' : 'none',
                    'orientation' => $exif['Orientation'] ?? null
                ];
                
                // Удаляем GPS данные из публичных метаданных для приватности
                if (isset($metadata['exif']['gps']) && $metadata['exif']['gps'] === 'present') {
                    $metadata['privacy_warning'] = 'GPS coordinates removed for privacy';
                }
            }
        }
        
        // Размеры изображения
        $imageInfo = getimagesize($filePath);
        if ($imageInfo) {
            $metadata['dimensions'] = [
                'width' => $imageInfo[0],
                'height' => $imageInfo[1],
                'aspect_ratio' => round($imageInfo[0] / $imageInfo[1], 2)
            ];
        }
        
        return $metadata;
    }
}
```

## ☁️ Интеграция с облачными хранилищами

### Мультиоблачная конфигурация

```bash
# Настройка AWS S3 для analytics_bot
php artisan teg:bot config analytics_bot --set media.storage_disk=s3_analytics
php artisan teg:bot config analytics_bot --set media.cdn_url="https://cdn.example.com"
php artisan teg:bot config analytics_bot --set media.auto_cdn_upload=true

# Настройка Google Cloud для media_bot
php artisan teg:bot config media_bot --set media.storage_disk=gcs_media
php artisan teg:bot config media_bot --set media.backup_disk=local_backup

# Настройка MinIO для shop_bot
php artisan teg:bot config shop_bot --set media.storage_disk=minio_shop
php artisan teg:bot config shop_bot --set media.public_access=true
```

### Cloud Storage Handler

```php
<?php
// app/TegBot/Media/CloudStorageHandler.php
namespace App\TegBot\Media;

use Illuminate\Support\Facades\Storage;
use AWS\S3\S3Client;

class CloudStorageHandler
{
    private string $botName;
    private array $config;
    
    public function __construct(string $botName)
    {
        $this->botName = $botName;
        $this->config = Bot::where('name', $botName)->first()->getMediaConfig();
    }
    
    public function uploadToCloud(MediaFile $mediaFile): void
    {
        $cloudDisk = $this->config['cloud_storage_disk'] ?? null;
        
        if (!$cloudDisk) {
            return;
        }
        
        try {
            // Загружаем основной файл
            $this->uploadFile($mediaFile->path, $cloudDisk);
            
            // Загружаем варианты (миниатюры, сжатые версии)
            if ($mediaFile->variants) {
                foreach ($mediaFile->variants as $variant) {
                    $this->uploadFile($variant['path'], $cloudDisk);
                }
            }
            
            // Обновляем URLs
            $this->updateCloudUrls($mediaFile, $cloudDisk);
            
            // Опционально удаляем локальные файлы
            if ($this->config['delete_local_after_upload'] ?? false) {
                $this->deleteLocalFiles($mediaFile);
            }
            
        } catch (\Exception $e) {
            logger()->error("Cloud upload failed for {$mediaFile->id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function uploadFile(string $localPath, string $cloudDisk): void
    {
        $localDisk = $this->config['storage_disk'] ?? 'public';
        $fileContent = Storage::disk($localDisk)->get($localPath);
        
        Storage::disk($cloudDisk)->put($localPath, $fileContent, [
            'visibility' => $this->config['cloud_visibility'] ?? 'public',
            'CacheControl' => 'max-age=31536000', // 1 год
            'ContentDisposition' => 'inline'
        ]);
    }
    
    private function updateCloudUrls(MediaFile $mediaFile, string $cloudDisk): void
    {
        $baseUrl = Storage::disk($cloudDisk)->url('');
        
        $cloudUrls = [
            'original' => $baseUrl . $mediaFile->path
        ];
        
        if ($mediaFile->variants) {
            foreach ($mediaFile->variants as $variantName => $variant) {
                $cloudUrls[$variantName] = $baseUrl . $variant['path'];
            }
        }
        
        $mediaFile->update(['cloud_urls' => $cloudUrls]);
    }
    
    public function generateSignedUrl(MediaFile $mediaFile, string $variant = 'original', int $expirationMinutes = 60): string
    {
        $cloudDisk = $this->config['cloud_storage_disk'] ?? null;
        
        if (!$cloudDisk) {
            return $this->getLocalUrl($mediaFile, $variant);
        }
        
        $path = $variant === 'original' 
            ? $mediaFile->path 
            : $mediaFile->variants[$variant]['path'] ?? $mediaFile->path;
        
        return Storage::disk($cloudDisk)->temporaryUrl($path, now()->addMinutes($expirationMinutes));
    }
    
    public function setupCDN(MediaFile $mediaFile): void
    {
        $cdnUrl = $this->config['cdn_url'] ?? null;
        
        if (!$cdnUrl) {
            return;
        }
        
        // Создаем CDN URLs
        $cdnUrls = [];
        $cloudUrls = $mediaFile->cloud_urls ?? [];
        
        foreach ($cloudUrls as $variant => $url) {
            $path = parse_url($url, PHP_URL_PATH);
            $cdnUrls[$variant] = rtrim($cdnUrl, '/') . $path;
        }
        
        $mediaFile->update(['cdn_urls' => $cdnUrls]);
        
        // Опционально: прогреваем CDN кэш
        if ($this->config['cdn_preload'] ?? false) {
            $this->preloadCDNCache($cdnUrls);
        }
    }
    
    private function preloadCDNCache(array $urls): void
    {
        foreach ($urls as $url) {
            // Отправляем HEAD запрос для прогрева кэша
            try {
                $context = stream_context_create([
                    'http' => [
                        'method' => 'HEAD',
                        'timeout' => 5
                    ]
                ]);
                @file_get_contents($url, false, $context);
            } catch (\Exception $e) {
                // Игнорируем ошибки прогрева
            }
        }
    }
}
```

## 📊 Аналитика медиа

### Статистика использования медиа

```bash
# Общая статистика медиа
php artisan teg:media stats

# Статистика по боту
php artisan teg:media stats shop_bot

# Детальная аналитика
php artisan teg:media analytics --period=30d --breakdown=type,size,bot

# Очистка старых файлов
php artisan teg:media cleanup --days=90 --dry-run
php artisan teg:media cleanup --days=90 --confirm

# Оптимизация хранилища
php artisan teg:media optimize --compress --remove-unused
```

### MediaAnalyticsService

```php
<?php
// app/TegBot/Media/MediaAnalyticsService.php
namespace App\TegBot\Media;

use App\Models\MediaFile;
use Illuminate\Support\Carbon;

class MediaAnalyticsService
{
    public function getOverallStats(): array
    {
        return [
            'total_files' => MediaFile::count(),
            'total_size' => $this->formatFileSize(MediaFile::sum('size')),
            'files_by_type' => $this->getFilesByType(),
            'files_by_bot' => $this->getFilesByBot(),
            'storage_distribution' => $this->getStorageDistribution(),
            'upload_trends' => $this->getUploadTrends(),
            'popular_formats' => $this->getPopularFormats()
        ];
    }
    
    public function getBotStats(string $botName): array
    {
        $query = MediaFile::where('bot_name', $botName);
        
        return [
            'total_files' => $query->count(),
            'total_size' => $this->formatFileSize($query->sum('size')),
            'avg_file_size' => $this->formatFileSize($query->avg('size')),
            'largest_file' => $this->formatFileSize($query->max('size')),
            'files_by_type' => $query->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'upload_activity' => $this->getBotUploadActivity($botName),
            'storage_usage' => $this->getBotStorageUsage($botName)
        ];
    }
    
    private function getUploadTrends(int $days = 30): array
    {
        $trends = MediaFile::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as uploads, SUM(size) as total_size')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return [
            'daily_uploads' => $trends->pluck('uploads', 'date')->toArray(),
            'daily_size' => $trends->pluck('total_size', 'date')->toArray(),
            'peak_day' => $trends->sortByDesc('uploads')->first(),
            'growth_rate' => $this->calculateGrowthRate($trends)
        ];
    }
    
    public function getStorageRecommendations(): array
    {
        $recommendations = [];
        
        // Поиск дубликатов
        $duplicates = $this->findDuplicateFiles();
        if ($duplicates->count() > 0) {
            $recommendations[] = [
                'type' => 'duplicates',
                'priority' => 'medium',
                'count' => $duplicates->count(),
                'potential_savings' => $this->formatFileSize($duplicates->sum('size')),
                'message' => 'Найдены дублирующиеся файлы'
            ];
        }
        
        // Поиск больших файлов
        $largeFiles = MediaFile::where('size', '>', 10 * 1024 * 1024)->get(); // >10MB
        if ($largeFiles->count() > 0) {
            $recommendations[] = [
                'type' => 'large_files',
                'priority' => 'low',
                'count' => $largeFiles->count(),
                'total_size' => $this->formatFileSize($largeFiles->sum('size')),
                'message' => 'Файлы большого размера можно дополнительно сжать'
            ];
        }
        
        // Неиспользуемые файлы
        $unusedFiles = MediaFile::where('last_accessed_at', '<', now()->subDays(90))
            ->orWhereNull('last_accessed_at')
            ->get();
        
        if ($unusedFiles->count() > 0) {
            $recommendations[] = [
                'type' => 'unused_files',
                'priority' => 'high',
                'count' => $unusedFiles->count(),
                'potential_savings' => $this->formatFileSize($unusedFiles->sum('size')),
                'message' => 'Файлы не использовались более 90 дней'
            ];
        }
        
        return $recommendations;
    }
    
    public function generateReport(string $botName = null, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $query = MediaFile::query();
        
        if ($botName) {
            $query->where('bot_name', $botName);
        }
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return [
            'period' => [
                'start' => $startDate?->format('Y-m-d') ?? 'all time',
                'end' => $endDate?->format('Y-m-d') ?? 'now',
                'bot' => $botName ?? 'all bots'
            ],
            'summary' => [
                'total_files' => $query->count(),
                'total_size' => $this->formatFileSize($query->sum('size')),
                'unique_users' => $query->distinct('user_id')->count(),
                'processing_success_rate' => $this->getProcessingSuccessRate($query)
            ],
            'breakdown' => [
                'by_type' => $this->getBreakdownByType($query),
                'by_size_range' => $this->getBreakdownBySize($query),
                'by_day' => $this->getBreakdownByDay($query),
                'by_user' => $this->getTopUploaders($query)
            ],
            'recommendations' => $this->getStorageRecommendations()
        ];
    }
}
```

## 🔧 CLI команды для медиа

### Управление медиа через командную строку

```bash
# Просмотр файлов
php artisan teg:media list --bot=shop_bot --type=image --limit=50
php artisan teg:media show {file_id}

# Обработка файлов
php artisan teg:media process --bot=shop_bot --reprocess-failed
php artisan teg:media thumbnail --regenerate --size=300x300

# Миграция в облако
php artisan teg:media migrate-to-cloud shop_bot --storage=s3_shop
php artisan teg:media sync-cloud --verify-integrity

# Очистка и оптимизация
php artisan teg:media cleanup --unused --days=90
php artisan teg:media compress --quality=80 --backup
php artisan teg:media deduplicate --dry-run

# Резервное копирование
php artisan teg:media backup --bot=shop_bot --storage=backup_disk
php artisan teg:media restore {backup_id} --verify

# Мониторинг
php artisan teg:media health-check
php artisan teg:media usage-report --email=admin@example.com
```

## 📚 Best Practices

### 🎯 Организация медиа файлов
1. **Используйте отдельные хранилища** для каждого бота
2. **Настройте автоматическую обработку** изображений и видео
3. **Генерируйте миниатюры** для быстрой загрузки
4. **Используйте CDN** для публичного контента

### 🔐 Безопасность
1. **Валидируйте все загружаемые файлы**
2. **Сканируйте на вирусы** критичный контент
3. **Удаляйте метаданные** с личной информацией
4. **Используйте подписанные URL** для приватных файлов

### ☁️ Облачное хранилище
1. **Настройте автоматическую загрузку** в облако
2. **Используйте правильные политики** доступа
3. **Настройте lifecycle** для старых файлов
4. **Мониторьте расходы** на хранение

### 📊 Оптимизация
1. **Регулярно очищайте** неиспользуемые файлы
2. **Сжимайте изображения** без потери качества
3. **Используйте современные форматы** (WebP, AVIF)
4. **Мониторьте дисковое пространство**

---

📁 **TegBot v2.0 Media** - Профессиональная обработка медиа для ваших ботов! 