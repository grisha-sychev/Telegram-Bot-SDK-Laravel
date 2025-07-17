# 📱 Обработка медиа в TegBot

## Обзор

TegBot v2.0 предоставляет мощные инструменты для работы с медиа файлами:

- 📸 **Фотографии**: Получение размеров, скачивание, сжатие
- 🎬 **Видео**: Информация о длительности, разрешении, миниатюрах  
- 📄 **Документы**: Работа с файлами любых типов
- 🎵 **Аудио**: Обработка голосовых сообщений и музыки
- 🎭 **Стикеры**: Анимированные и статичные стикеры
- 🌐 **Автоматическая валидация**: Проверка размера, типа, безопасности

## Типы медиа

### Поддерживаемые типы

```php
public function getMessageType(): string
{
    // Возвращает один из типов:
    // 'text', 'photo', 'video', 'document', 'audio', 
    // 'voice', 'sticker', 'animation', 'contact', 'location'
}
```

### Проверка типа сообщения

```php
public function main(): void
{
    $messageType = $this->getMessageType();
    
    switch ($messageType) {
        case 'photo':
            $this->handlePhoto();
            break;
        case 'video':
            $this->handleVideo();
            break;
        case 'document':
            $this->handleDocument();
            break;
        case 'audio':
            $this->handleAudio();
            break;
        case 'voice':
            $this->handleVoice();
            break;
        case 'sticker':
            $this->handleSticker();
            break;
        default:
            $this->handleText();
    }
}
```

## Работа с фотографиями

### Получение информации о фото

```php
private function handlePhoto(): void
{
    $photoInfo = $this->getPhotoInfo();
    
    if (!$photoInfo) {
        $this->sendSelf('❌ Ошибка получения информации о фото');
        return;
    }
    
    // Структура $photoInfo:
    // [
    //     'file_id' => 'string',
    //     'file_unique_id' => 'string', 
    //     'count' => int,              // количество размеров
    //     'sizes' => [                 // массив размеров
    //         [
    //             'width' => int,
    //             'height' => int,
    //             'file_size' => int,
    //             'file_id' => 'string'
    //         ]
    //     ],
    //     'largest' => [               // самый большой размер
    //         'width' => int,
    //         'height' => int,
    //         'file_size' => int,
    //         'file_id' => 'string'
    //     ]
    // ]
    
    $largest = $photoInfo['largest'];
    $message = "📸 **Получено фото**\n\n";
    $message .= "📐 Размер: {$largest['width']}x{$largest['height']}\n";
    $message .= "💾 Размер файла: " . $this->formatFileSize($largest['file_size']) . "\n";
    $message .= "🔢 Доступно размеров: {$photoInfo['count']}";
    
    $this->sendSelf($message);
}
```

### Скачивание фото

```php
private function downloadPhoto(): void
{
    $photoInfo = $this->getPhotoInfo();
    
    if (!$photoInfo) return;
    
    // Скачиваем самый большой размер
    $fileId = $photoInfo['largest']['file_id'];
    $downloadPath = storage_path('app/tegbot/photos/');
    
    try {
        $filePath = $this->downloadFile($fileId, $downloadPath);
        
        if ($filePath) {
            $this->sendSelf("✅ Фото сохранено: " . basename($filePath));
            
            // Можно дополнительно обработать файл
            $this->processImage($filePath);
        }
    } catch (Exception $e) {
        $this->logError('Photo download failed', $e);
        $this->sendSelf('❌ Ошибка при скачивании фото');
    }
}

private function processImage(string $filePath): void
{
    // Пример обработки изображения
    $imageInfo = getimagesize($filePath);
    
    if ($imageInfo) {
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo['mime'];
        
        $this->logActivity('image_processed', [
            'width' => $width,
            'height' => $height,
            'type' => $type,
            'file_size' => filesize($filePath),
        ]);
    }
}
```

## Работа с видео

### Получение информации о видео

```php
private function handleVideo(): void
{
    $videoInfo = $this->getVideoInfo();
    
    if (!$videoInfo) {
        $this->sendSelf('❌ Ошибка получения информации о видео');
        return;
    }
    
    // Структура $videoInfo:
    // [
    //     'file_id' => 'string',
    //     'file_unique_id' => 'string',
    //     'width' => int,
    //     'height' => int,
    //     'duration' => int,           // в секундах
    //     'thumbnail' => [             // миниатюра (если есть)
    //         'file_id' => 'string',
    //         'width' => int,
    //         'height' => int,
    //         'file_size' => int
    //     ],
    //     'file_name' => 'string',     // может отсутствовать
    //     'mime_type' => 'string',     // может отсутствовать
    //     'file_size' => int
    // ]
    
    $message = "🎬 **Получено видео**\n\n";
    $message .= "📐 Разрешение: {$videoInfo['width']}x{$videoInfo['height']}\n";
    $message .= "⏱️ Длительность: " . $this->formatDuration($videoInfo['duration']) . "\n";
    $message .= "💾 Размер: " . $this->formatFileSize($videoInfo['file_size']) . "\n";
    
    if (isset($videoInfo['file_name'])) {
        $message .= "📁 Имя файла: {$videoInfo['file_name']}\n";
    }
    
    if (isset($videoInfo['mime_type'])) {
        $message .= "🏷️ Тип: {$videoInfo['mime_type']}\n";
    }
    
    $this->sendSelf($message);
}

private function formatDuration(int $seconds): string
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
    }
    
    return sprintf('%d:%02d', $minutes, $seconds);
}
```

### Скачивание видео с проверками

```php
private function downloadVideo(): void
{
    $videoInfo = $this->getVideoInfo();
    
    if (!$videoInfo) return;
    
    // Проверка размера файла (максимум 50MB)
    if ($videoInfo['file_size'] > 50 * 1024 * 1024) {
        $this->sendSelf('❌ Видео слишком большое (максимум 50MB)');
        return;
    }
    
    // Проверка длительности (максимум 10 минут)
    if ($videoInfo['duration'] > 600) {
        $this->sendSelf('❌ Видео слишком длинное (максимум 10 минут)');
        return;
    }
    
    $fileId = $videoInfo['file_id'];
    $downloadPath = storage_path('app/tegbot/videos/');
    
    try {
        $this->sendSelf('⬇️ Скачиваю видео...');
        
        $filePath = $this->downloadFile($fileId, $downloadPath);
        
        if ($filePath) {
            $this->sendSelf('✅ Видео сохранено!');
            
            // Асинхронная обработка видео
            $this->processVideoAsync($filePath, $videoInfo);
        }
    } catch (Exception $e) {
        $this->logError('Video download failed', $e);
        $this->sendSelf('❌ Ошибка при скачивании видео');
    }
}
```

## Работа с документами

### Обработка файлов

```php
private function handleDocument(): void
{
    $documentInfo = $this->getDocumentInfo();
    
    if (!$documentInfo) {
        $this->sendSelf('❌ Ошибка получения информации о документе');
        return;
    }
    
    // Структура $documentInfo:
    // [
    //     'file_id' => 'string',
    //     'file_unique_id' => 'string',
    //     'file_name' => 'string',     // может отсутствовать
    //     'mime_type' => 'string',     // может отсутствовать
    //     'file_size' => int,
    //     'thumbnail' => [...]         // может отсутствовать
    // ]
    
    $fileName = $documentInfo['file_name'] ?? 'Документ без имени';
    $fileSize = $this->formatFileSize($documentInfo['file_size']);
    $mimeType = $documentInfo['mime_type'] ?? 'Неизвестный тип';
    
    $message = "📄 **Получен документ**\n\n";
    $message .= "📁 Имя файла: {$fileName}\n";
    $message .= "💾 Размер: {$fileSize}\n";
    $message .= "🏷️ Тип: {$mimeType}\n";
    
    // Проверка типа файла
    if ($this->isAllowedFileType($mimeType)) {
        $message .= "\n✅ Файл разрешен к скачиванию";
        
        // Предложить скачать
        $this->sendSelfInline($message, [
            ['callback:download_doc:' . $documentInfo['file_id'], '⬇️ Скачать'],
            ['callback:info_doc:' . $documentInfo['file_id'], 'ℹ️ Подробнее'],
        ]);
    } else {
        $message .= "\n❌ Тип файла не разрешен";
        $this->sendSelf($message);
    }
}

private function isAllowedFileType(string $mimeType): bool
{
    $allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'image/jpeg',
        'image/png',
        'image/gif',
    ];
    
    return in_array($mimeType, $allowedTypes);
}
```

## Работа с аудио и голосовыми сообщениями

### Голосовые сообщения

```php
private function handleVoice(): void
{
    // Получение информации о голосовом сообщении
    $voice = $this->update['message']['voice'] ?? null;
    
    if (!$voice) return;
    
    $duration = $voice['duration'];
    $fileSize = $voice['file_size'] ?? 0;
    
    $message = "🎤 **Голосовое сообщение**\n\n";
    $message .= "⏱️ Длительность: " . $this->formatDuration($duration) . "\n";
    $message .= "💾 Размер: " . $this->formatFileSize($fileSize);
    
    $this->sendSelf($message);
    
    // Можно добавить обработку голоса (STT)
    if ($duration < 60) { // только короткие сообщения
        $this->processVoiceMessage($voice['file_id']);
    }
}

private function processVoiceMessage(string $fileId): void
{
    // Здесь можно интегрировать Speech-to-Text
    $this->sendSelf('🔄 Обрабатываю голосовое сообщение...');
    
    // Пример интеграции с внешним API
    // $text = $this->speechToText($fileId);
    // if ($text) {
    //     $this->sendSelf("📝 Распознанный текст:\n{$text}");
    // }
}
```

### Аудио файлы

```php
private function handleAudio(): void
{
    $audio = $this->update['message']['audio'] ?? null;
    
    if (!$audio) return;
    
    $duration = $audio['duration'];
    $title = $audio['title'] ?? 'Без названия';
    $performer = $audio['performer'] ?? 'Неизвестный исполнитель';
    $fileSize = $audio['file_size'] ?? 0;
    
    $message = "🎵 **Аудио файл**\n\n";
    $message .= "🎼 Название: {$title}\n";
    $message .= "👤 Исполнитель: {$performer}\n";
    $message .= "⏱️ Длительность: " . $this->formatDuration($duration) . "\n";
    $message .= "💾 Размер: " . $this->formatFileSize($fileSize);
    
    $this->sendSelf($message);
}
```

## Обработка медиа с подписями

### Универсальный обработчик

```php
public function main(): void
{
    // Регистрируем обработчик медиа с подписями
    $this->mediaWithCaption(function ($mediaInfo, $caption) {
        $this->handleMediaWithCaption($mediaInfo, $caption);
    });
    
    // Остальная логика...
}

private function handleMediaWithCaption(array $mediaInfo, string $caption): void
{
    $type = $mediaInfo['type'];
    $data = $mediaInfo['data'];
    
    // Логируем получение медиа
    $this->logActivity('media_received', [
        'type' => $type,
        'caption_length' => strlen($caption),
        'user_id' => $this->getUserId,
    ]);
    
    // Проверяем подпись на команды
    if (str_starts_with($caption, '/')) {
        // Обрабатываем как команду
        $this->handleCommand($caption);
        return;
    }
    
    // Обрабатываем в зависимости от типа
    switch ($type) {
        case 'photo':
            $this->handlePhotoWithCaption($data, $caption);
            break;
        case 'video':
            $this->handleVideoWithCaption($data, $caption);
            break;
        case 'document':
            $this->handleDocumentWithCaption($data, $caption);
            break;
        default:
            $this->handleGenericMediaWithCaption($mediaInfo, $caption);
    }
}

private function handlePhotoWithCaption(array $photoData, string $caption): void
{
    // Специальная обработка фото с подписью
    $response = "📸 **Фото с подписью**\n\n";
    $response .= "💬 Подпись: {$caption}\n";
    $response .= "📐 Размеров: {$photoData['count']}\n";
    
    // Анализ подписи
    if (str_contains(strtolower($caption), 'сохранить')) {
        $this->downloadPhoto();
        $response .= "\n💾 Фото сохранено!";
    }
    
    $this->sendSelf($response);
}
```

## Скачивание и сохранение файлов

### Универсальный метод скачивания

```php
public function downloadFile(string $fileId, string $downloadPath = null, string $fileName = null): ?string
{
    try {
        // Получаем информацию о файле
        $fileInfo = $this->getFileInfo($fileId);
        
        if (!$fileInfo || !isset($fileInfo['file_path'])) {
            throw new Exception('Не удалось получить путь к файлу');
        }
        
        // Формируем URL для скачивания
        $fileUrl = "https://api.telegram.org/file/bot{$this->botToken}/{$fileInfo['file_path']}";
        
        // Устанавливаем путь для сохранения
        if (!$downloadPath) {
            $downloadPath = storage_path('app/tegbot/downloads/');
        }
        
        // Создаем папку если не существует
        if (!is_dir($downloadPath)) {
            mkdir($downloadPath, 0755, true);
        }
        
        // Генерируем имя файла
        if (!$fileName) {
            $extension = pathinfo($fileInfo['file_path'], PATHINFO_EXTENSION);
            $fileName = $fileId . ($extension ? ".{$extension}" : '');
        }
        
        $fullPath = $downloadPath . $fileName;
        
        // Скачиваем файл
        $fileContent = $this->downloadFileContent($fileUrl);
        
        if ($fileContent === false) {
            throw new Exception('Ошибка скачивания файла');
        }
        
        // Сохраняем файл
        if (file_put_contents($fullPath, $fileContent) === false) {
            throw new Exception('Ошибка сохранения файла');
        }
        
        // Логируем успешное скачивание
        $this->logActivity('file_downloaded', [
            'file_id' => $fileId,
            'file_path' => $fullPath,
            'file_size' => strlen($fileContent),
            'user_id' => $this->getUserId,
        ]);
        
        return $fullPath;
        
    } catch (Exception $e) {
        $this->logError('File download failed', $e, [
            'file_id' => $fileId,
            'download_path' => $downloadPath,
        ]);
        
        return null;
    }
}

private function downloadFileContent(string $url): string|false
{
    $context = stream_context_create([
        'http' => [
            'timeout' => 30, // 30 секунд таймаут
            'user_agent' => 'TegBot/2.0',
        ]
    ]);
    
    return file_get_contents($url, false, $context);
}
```

## Валидация и безопасность

### Проверка файлов

```php
private function validateMediaFile(array $fileInfo): bool
{
    $maxSize = config('tegbot.files.max_file_size', 20 * 1024 * 1024); // 20MB
    
    // Проверка размера
    if (isset($fileInfo['file_size']) && $fileInfo['file_size'] > $maxSize) {
        $this->sendSelf('❌ Файл слишком большой (максимум ' . $this->formatFileSize($maxSize) . ')');
        return false;
    }
    
    // Проверка типа файла по MIME
    if (isset($fileInfo['mime_type'])) {
        $dangerousTypes = [
            'application/x-executable',
            'application/x-msdownload',
            'application/x-msdos-program',
        ];
        
        if (in_array($fileInfo['mime_type'], $dangerousTypes)) {
            $this->sendSelf('❌ Данный тип файла запрещен');
            return false;
        }
    }
    
    return true;
}
```

### Сканирование на вирусы

```php
private function scanFileForViruses(string $filePath): bool
{
    // Интеграция с ClamAV или другим антивирусом
    try {
        $command = "clamscan --quiet --infected {$filePath}";
        $result = shell_exec($command);
        
        if ($result !== null) {
            // Файл заражен
            $this->logActivity('virus_detected', [
                'file_path' => $filePath,
                'user_id' => $this->getUserId,
            ]);
            
            unlink($filePath); // Удаляем зараженный файл
            $this->sendSelf('🦠 Обнаружен вирус! Файл удален.');
            return false;
        }
        
        return true;
        
    } catch (Exception $e) {
        $this->logError('Virus scan failed', $e);
        return false; // В случае ошибки лучше заблокировать
    }
}
```

## Утилиты

### Форматирование размера файла

```php
private function formatFileSize(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}
```

### Определение типа файла по расширению

```php
private function getFileTypeByExtension(string $fileName): string
{
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    $types = [
        'jpg' => 'image', 'jpeg' => 'image', 'png' => 'image', 'gif' => 'image',
        'mp4' => 'video', 'avi' => 'video', 'mov' => 'video', 'mkv' => 'video',
        'mp3' => 'audio', 'wav' => 'audio', 'flac' => 'audio', 'ogg' => 'audio',
        'pdf' => 'document', 'doc' => 'document', 'docx' => 'document',
        'xls' => 'spreadsheet', 'xlsx' => 'spreadsheet',
        'txt' => 'text', 'csv' => 'text',
        'zip' => 'archive', 'rar' => 'archive', '7z' => 'archive',
    ];
    
    return $types[$extension] ?? 'unknown';
}
```

## Примеры использования

### Бот для обработки изображений

```php
class ImageProcessorBot extends AdstractBot
{
    public function main(): void
    {
        if ($this->getMessageType() === 'photo') {
            $this->processImage();
        }
    }
    
    private function processImage(): void
    {
        $photoInfo = $this->getPhotoInfo();
        
        if (!$photoInfo) return;
        
        $this->sendSelf('🖼️ Обрабатываю изображение...');
        
        // Скачиваем файл
        $filePath = $this->downloadFile($photoInfo['largest']['file_id']);
        
        if ($filePath) {
            // Создаем миниатюру
            $thumbnailPath = $this->createThumbnail($filePath);
            
            // Отправляем результат
            $this->sendPhoto($thumbnailPath, 'Миниатюра готова!');
        }
    }
}
```

### Бот для работы с документами

```php
class DocumentManagerBot extends AdstractBot
{
    public function main(): void
    {
        if ($this->getMessageType() === 'document') {
            $this->manageDocument();
        }
    }
    
    private function manageDocument(): void
    {
        $docInfo = $this->getDocumentInfo();
        
        if (!$this->validateDocument($docInfo)) {
            return;
        }
        
        // Сохраняем в каталог
        $this->saveToLibrary($docInfo);
        
        // Отправляем подтверждение
        $this->sendSelf('📚 Документ добавлен в библиотеку!');
    }
}
```

---

📱 **Медиа обработка в TegBot** - мощный инструмент для работы с любыми типами файлов! 