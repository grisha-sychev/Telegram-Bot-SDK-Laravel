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
        Schema::create('tegbot_bots', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Уникальное имя бота');
            $table->string('token')->unique()->comment('Токен бота от BotFather');
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tegbot_bots');
    }
}; 