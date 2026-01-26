<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Удалить старое ограничение
            $table->dropUnique(['telegram_id']);
            
            // Добавить составное уникальное ограничение
            $table->unique(['project_id', 'telegram_id']);
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['project_id', 'telegram_id']);
            $table->unique('telegram_id');
        });
    }
};
