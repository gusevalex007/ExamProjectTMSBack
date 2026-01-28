<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Прогресс прохождения курсов/уроков
     */
    public function up(): void
    {
        Schema::create('user_progress', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID зарегистрированного пользователя');

            $table->string('telegram_id', 50)
                ->nullable()
                ->comment('Telegram ID для TMA пользователей');

            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade')
                ->comment('ID курса или урока');

            $table->integer('progress')
                ->default(0)
                ->comment('Прогресс 0-100%');

            $table->timestamp('completed_at')
                ->nullable()
                ->comment('Дата завершения');

            $table->json('metadata')
                ->nullable()
                ->comment('Дополнительные данные (время просмотра, ответы и т.д.)');

            $table->timestamps();

            $table->index(['user_id', 'product_id']);
            $table->index(['telegram_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_progress');
    }
};