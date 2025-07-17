<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use App\Models\UserTelegram;
use Carbon\Carbon;

class CleanupStatesCommand extends Command
{
    /**
     * Название и сигнатура консольной команды.
     */
    protected $signature = 'states:cleanup {--days=30 : Количество дней для хранения сообщений} {--dry-run : Запуск без внесения изменений}';

    /**
     * Описание консольной команды.
     */
    protected $description = 'Очистка старых состояний и сообщений бота';

    /**
     * Выполнение консольной команды.
     */
    public function handle()
    {
        $days = $this->option('days');
        $isDryRun = $this->option('dry-run');

        $this->info("🧹 Начинаем очистку состояний (старше {$days} дней)");
        
        if ($isDryRun) {
            $this->warn("🔍 РЕЖИМ ПРОБНОГО ЗАПУСКА - Изменения не будут внесены");
        }

        // Получаем статистику перед очисткой
        $oldMessagesCount = Message::where('updated_at', '<', Carbon::now()->subDays($days))->count();
        $totalMessages = Message::count();
        $totalUsers = UserTelegram::count();

        $this->table(
            ['Метрика', 'Количество'],
            [
                ['Всего пользователей', number_format($totalUsers)],
                ['Всего сообщений', number_format($totalMessages)],
                ['Старые сообщения (к удалению)', number_format($oldMessagesCount)],
                ['Сообщения к сохранению', number_format($totalMessages - $oldMessagesCount)],
            ]
        );

        if ($oldMessagesCount === 0) {
            $this->info("✅ Старые сообщения не найдены. Очистка не требуется.");
            return self::SUCCESS;
        }

        if (!$isDryRun && !$this->confirm("Хотите удалить {$oldMessagesCount} старых сообщений?")) {
            $this->info("❌ Очистка отменена пользователем.");
            return self::SUCCESS;
        }

        if (!$isDryRun) {
            $this->info("🚀 Начинаем очистку...");
            
            $bar = $this->output->createProgressBar(3);
            $bar->start();

            // Очищаем старые сообщения
            $deletedMessages = Message::cleanOldMessages($days);
            $bar->advance();

            // Очищаем записи пользователей-сирот (опционально - пользователи без недавней активности)
            $inactiveUsers = UserTelegram::whereDoesntHave('messages', function ($query) use ($days) {
                $query->where('created_at', '>=', Carbon::now()->subDays($days * 2));
            })->where('created_at', '<', Carbon::now()->subDays($days * 2));
            
            $inactiveUsersCount = $inactiveUsers->count();
            $bar->advance();

            // Опционально: Удалить полностью неактивных пользователей (раскомментировать при необходимости)
            // $deletedUsers = $inactiveUsers->delete();
            $deletedUsers = 0; // Пока сохраняем пользователей
            $bar->advance();

            $bar->finish();
            $this->newLine(2);

            $this->info("✅ Очистка завершена!");
            $this->table(
                ['Операция', 'Количество'],
                [
                    ['Удалено сообщений', number_format($deletedMessages)],
                    ['Найдено неактивных пользователей', number_format($inactiveUsersCount)],
                    ['Удалено пользователей', number_format($deletedUsers)],
                ]
            );

            // Логируем активность очистки
            \Log::info('Очистка состояний завершена', [
                'days' => $days,
                'deleted_messages' => $deletedMessages,
                'inactive_users' => $inactiveUsersCount,
                'deleted_users' => $deletedUsers,
            ]);
        } else {
            $this->info("✅ Пробный запуск завершен. Было бы удалено {$oldMessagesCount} сообщений.");
        }

        // Показываем оценку экономии памяти и размера базы данных
        $this->newLine();
        $this->info("💾 Предполагаемая экономия:");
        $estimatedSize = $oldMessagesCount * 0.5; // Примерная оценка: 0.5КБ на сообщение
        $this->line("  • Размер базы данных: ~" . number_format($estimatedSize, 1) . " КБ");
        $this->line("  • Использование памяти: Сокращение времени запросов и потребления памяти");

        return self::SUCCESS;
    }
} 