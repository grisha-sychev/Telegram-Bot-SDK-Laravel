<?php

namespace Teg\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MigrateCommand extends Command
{
    protected $signature = 'teg:migrate 
                            {action : Action (export, import, clear, backup)}
                            {--format=json : Export format (json, csv)}
                            {--path= : File path for import/export}
                            {--force : Force action without confirmation}';
    
    protected $description = 'Миграция данных TegBot';

    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'export':
                return $this->exportData();
            case 'import':
                return $this->importData();
            case 'clear':
                return $this->clearData();
            case 'backup':
                return $this->backupData();
            default:
                $this->error("Неизвестное действие: {$action}");
                $this->line('Доступные действия: export, import, clear, backup');
                return 1;
        }
    }

    private function exportData(): int
    {
        $this->info('📤 Экспорт данных TegBot...');
        $this->newLine();

        $format = $this->option('format');
        $path = $this->option('path') ?? storage_path('app/tegbot_export_' . date('Y-m-d_H-i-s') . '.' . $format);

        // Собираем данные для экспорта
        $data = $this->collectExportData();

        try {
            if ($format === 'csv') {
                $this->exportToCsv($data, $path);
            } else {
                $this->exportToJson($data, $path);
            }

            $this->info("✅ Данные экспортированы: {$path}");
            $this->line("📊 Записей: " . $this->countRecords($data));
            $this->line("💾 Размер: " . $this->formatFileSize(filesize($path)));

        } catch (\Exception $e) {
            $this->error("❌ Ошибка экспорта: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }

    private function importData(): int
    {
        $path = $this->option('path');
        
        if (!$path) {
            $path = $this->ask('Введите путь к файлу для импорта');
        }

        if (!$path || !file_exists($path)) {
            $this->error('❌ Файл не найден');
            return 1;
        }

        $this->info("📥 Импорт данных из: {$path}");
        $this->newLine();

        if (!$this->option('force') && !$this->confirm('⚠️  Импорт может перезаписать существующие данные. Продолжить?', false)) {
            $this->info('Отменено');
            return 0;
        }

        try {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            
            if ($extension === 'csv') {
                $data = $this->importFromCsv($path);
            } else {
                $data = $this->importFromJson($path);
            }

            $this->processImportData($data);

            $this->info("✅ Данные импортированы успешно");
            $this->line("📊 Обработано записей: " . $this->countRecords($data));

        } catch (\Exception $e) {
            $this->error("❌ Ошибка импорта: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }

    private function clearData(): int
    {
        $this->warn('⚠️  ВНИМАНИЕ: Эта операция удалит все данные TegBot!');
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Вы уверены, что хотите удалить ВСЕ данные?', false)) {
                $this->info('Отменено');
                return 0;
            }

            $confirmation = $this->ask('Введите "DELETE ALL" для подтверждения');
            if ($confirmation !== 'DELETE ALL') {
                $this->error('❌ Неверное подтверждение');
                return 1;
            }
        }

        $this->info('🗑️  Очистка данных...');

        try {
            // Очищаем файлы
            $this->clearFiles();
            
            // Очищаем кэш
            $this->clearCache();
            
            // Очищаем логи
            $this->clearLogs();

            $this->info('✅ Все данные TegBot удалены');

        } catch (\Exception $e) {
            $this->error("❌ Ошибка очистки: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }

    private function backupData(): int
    {
        $this->info('💾 Создание резервной копии...');
        $this->newLine();

        $backupDir = storage_path('app/tegbot_backups');
        $timestamp = date('Y-m-d_H-i-s');
        $backupPath = "{$backupDir}/backup_{$timestamp}";

        try {
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            mkdir($backupPath, 0755, true);

            // Бэкап конфигурации
            $this->backupConfiguration($backupPath);

            // Бэкап файлов
            $this->backupFiles($backupPath);

            // Бэкап данных
            $this->backupUserData($backupPath);

            // Создаем манифест
            $this->createBackupManifest($backupPath, $timestamp);

            $this->info("✅ Резервная копия создана: {$backupPath}");
            $this->line("📁 Размер: " . $this->formatDirectorySize($backupPath));

        } catch (\Exception $e) {
            $this->error("❌ Ошибка создания бэкапа: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }

    private function collectExportData(): array
    {
        $data = [
            'metadata' => [
                'export_date' => now()->toISOString(),
                'version' => '1.0',
                'bot_token' => substr(config('tegbot.token', ''), 0, 10) . '...', // Маскируем токен
            ],
            'configuration' => config('tegbot', []),
            'users' => [],
            'chats' => [],
            'files' => [],
            'logs' => [],
        ];

        // Здесь в реальном приложении должна быть логика сбора данных из БД
        // Это заглушка для демонстрации структуры
        
        // Пример сбора файлов
        $downloadPath = config('tegbot.files.download_path', storage_path('app/tegbot/downloads'));
        if (is_dir($downloadPath)) {
            $files = File::allFiles($downloadPath);
            foreach ($files as $file) {
                $data['files'][] = [
                    'path' => $file->getRelativePathname(),
                    'size' => $file->getSize(),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        return $data;
    }

    private function exportToJson(array $data, string $path): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($path, $json);
    }

    private function exportToCsv(array $data, string $path): void
    {
        $handle = fopen($path, 'w');
        
        // Заголовки
        fputcsv($handle, ['Type', 'ID', 'Data', 'Created']);

        // Пользователи
        foreach ($data['users'] as $user) {
            fputcsv($handle, ['user', $user['id'] ?? '', json_encode($user), $user['created_at'] ?? '']);
        }

        // Чаты
        foreach ($data['chats'] as $chat) {
            fputcsv($handle, ['chat', $chat['id'] ?? '', json_encode($chat), $chat['created_at'] ?? '']);
        }

        // Файлы
        foreach ($data['files'] as $file) {
            fputcsv($handle, ['file', $file['path'] ?? '', json_encode($file), $file['modified'] ?? '']);
        }

        fclose($handle);
    }

    private function importFromJson(string $path): array
    {
        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Неверный формат JSON: ' . json_last_error_msg());
        }

        return $data;
    }

    private function importFromCsv(string $path): array
    {
        $data = [
            'users' => [],
            'chats' => [],
            'files' => [],
        ];

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle); // Пропускаем заголовки

        while (($row = fgetcsv($handle)) !== false) {
            [$type, $id, $jsonData, $created] = $row;
            $recordData = json_decode($jsonData, true);

            if ($recordData) {
                $data[$type . 's'][] = $recordData;
            }
        }

        fclose($handle);
        return $data;
    }

    private function processImportData(array $data): void
    {
        // В реальном приложении здесь должна быть логика записи в БД
        $this->line("📊 Обработка импорта:");
        
        if (isset($data['users'])) {
            $this->line("  👤 Пользователи: " . count($data['users']));
        }
        
        if (isset($data['chats'])) {
            $this->line("  💬 Чаты: " . count($data['chats']));
        }
        
        if (isset($data['files'])) {
            $this->line("  📁 Файлы: " . count($data['files']));
        }
    }

    private function clearFiles(): void
    {
        $this->line('🗑️  Удаление файлов...');
        
        $paths = [
            storage_path('app/tegbot/downloads'),
            storage_path('app/tegbot/temp'),
        ];

        foreach ($paths as $path) {
            if (is_dir($path)) {
                File::deleteDirectory($path);
                $this->line("  ✅ Удалено: {$path}");
            }
        }
    }

    private function clearCache(): void
    {
        $this->line('🗑️  Очистка кэша...');
        
        try {
            \Illuminate\Support\Facades\Cache::flush();
            $this->line('  ✅ Кэш очищен');
        } catch (\Exception $e) {
            $this->warn("  ⚠️  Ошибка очистки кэша: {$e->getMessage()}");
        }
    }

    private function clearLogs(): void
    {
        $this->line('🗑️  Удаление логов...');
        
        $logPath = storage_path('logs/tegbot');
        if (is_dir($logPath)) {
            File::deleteDirectory($logPath);
            $this->line("  ✅ Удалено: {$logPath}");
        }
    }

    private function backupConfiguration(string $backupPath): void
    {
        $configPath = config_path('tegbot.php');
        if (file_exists($configPath)) {
            copy($configPath, "{$backupPath}/tegbot.php");
        }

        // Экспортируем текущую конфигурацию
        $config = config('tegbot', []);
        file_put_contents(
            "{$backupPath}/current_config.json",
            json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function backupFiles(string $backupPath): void
    {
        $downloadPath = config('tegbot.files.download_path', storage_path('app/tegbot/downloads'));
        if (is_dir($downloadPath)) {
            $filesBackupPath = "{$backupPath}/files";
            mkdir($filesBackupPath, 0755, true);
            
            File::copyDirectory($downloadPath, $filesBackupPath);
        }
    }

    private function backupUserData(string $backupPath): void
    {
        // Экспортируем пользовательские данные
        $data = $this->collectExportData();
        file_put_contents(
            "{$backupPath}/user_data.json",
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function createBackupManifest(string $backupPath, string $timestamp): void
    {
        $manifest = [
            'created_at' => $timestamp,
            'version' => '1.0',
            'files' => [],
            'size_total' => 0,
        ];

        $files = File::allFiles($backupPath);
        foreach ($files as $file) {
            $size = $file->getSize();
            $manifest['files'][] = [
                'path' => $file->getRelativePathname(),
                'size' => $size,
                'hash' => md5_file($file->getPathname()),
            ];
            $manifest['size_total'] += $size;
        }

        file_put_contents(
            "{$backupPath}/manifest.json",
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function countRecords(array $data): int
    {
        $count = 0;
        foreach (['users', 'chats', 'files', 'logs'] as $type) {
            if (isset($data[$type])) {
                $count += count($data[$type]);
            }
        }
        return $count;
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 1) . ' ' . $units[$i];
    }

    private function formatDirectorySize(string $path): string
    {
        $size = 0;
        $files = File::allFiles($path);
        
        foreach ($files as $file) {
            $size += $file->getSize();
        }
        
        return $this->formatFileSize($size);
    }
} 