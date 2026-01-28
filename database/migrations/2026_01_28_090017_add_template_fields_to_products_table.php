<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Для LMS
            $table->text('video_url')->nullable()->after('images');
            $table->json('lessons')->nullable()->after('video_url')
                ->comment('Структура уроков для LMS: [{title, duration, video_url, description}]');
            $table->integer('duration_hours')->nullable()->after('lessons')
                ->comment('Длительность курса в часах');
            
            // Для Digital Products
            $table->string('file_url')->nullable()->after('duration_hours')
                ->comment('Ссылка на скачивание файла');
            $table->string('file_type', 50)->nullable()->after('file_url')
                ->comment('Тип файла: pdf, video, audio, software, ebook');
            $table->decimal('file_size_mb', 8, 2)->nullable()->after('file_type')
                ->comment('Размер файла в MB');
            
            // Универсальное поле типа
            $table->string('product_type', 50)->default('physical')->after('project_id')
                ->comment('physical, course, digital');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'video_url', 'lessons', 'duration_hours',
                'file_url', 'file_type', 'file_size_mb', 'product_type'
            ]);
        });
    }
};
