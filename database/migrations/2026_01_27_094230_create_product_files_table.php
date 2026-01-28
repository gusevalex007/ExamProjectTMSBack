<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Таблица для цифровых файлов (Digital Products, LMS видео/материалы)
     */
    public function up(): void
    {
        Schema::create('product_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade')
                ->comment('ID продукта');

            $table->string('file_url', 500)
                ->comment('URL файла (S3, local storage)');

            $table->string('file_name', 255)
                ->nullable()
                ->comment('Оригинальное имя файла');

            $table->string('file_type', 50)
                ->nullable()
                ->comment('Тип: pdf, video, zip, audio, license_key');

            $table->bigInteger('file_size')
                ->nullable()
                ->comment('Размер в байтах');

            $table->integer('download_count')
                ->default(0)
                ->comment('Счётчик скачиваний');

            $table->boolean('is_active')
                ->default(true)
                ->comment('Доступен для скачивания');

            $table->timestamps();

            $table->index(['product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_files');
    }
};