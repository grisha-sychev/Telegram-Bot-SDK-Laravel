<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bots', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Уникальное имя бота');
            $table->string('dev_token')->nullable()->comment('Токен бота для разработки');
            $table->string('prod_token')->nullable()->comment('Токен бота для продакшена');
            $table->string('username')->nullable()->comment('Username бота (@botname)');
            $table->string('first_name')->comment('Имя бота');
            $table->text('description')->nullable()->comment('Описание бота');
            $table->bigInteger('bot_id')->unique()->comment('ID бота в Telegram');
            $table->boolean('enabled')->default(true)->comment('Активен ли бот');
            $table->string('webhook_url')->nullable()->comment('URL webhook');
            $table->string('webhook_secret')->nullable()->comment('Секрет webhook');
            $table->json('settings')->nullable()->comment('Дополнительные настройки');
            $table->json('admin_ids')->nullable()->comment('ID администраторов бота');
            $table->timestamps();

            $table->index(['enabled']);
            $table->index(['name']);
        });

        // Если таблица уже существует, обновляем её структуру
        if (Schema::hasTable('bots')) {
            Schema::table('bots', function (Blueprint $table) {
                // Проверяем существование старого поля token
                if (Schema::hasColumn('bots', 'token')) {
                    // Переименовываем старый токен в dev_token
                    $table->renameColumn('token', 'dev_token');
                }
                
                // Добавляем новые поля, если их нет
                if (!Schema::hasColumn('bots', 'dev_token')) {
                    $table->string('dev_token')->nullable()->comment('Токен бота для разработки');
                }
                
                if (!Schema::hasColumn('bots', 'prod_token')) {
                    $table->string('prod_token')->nullable()->comment('Токен бота для продакшена');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bots');
    }
}; 