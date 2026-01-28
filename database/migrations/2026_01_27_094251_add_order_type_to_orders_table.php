<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Добавляем тип заказа для различения типов транзакций
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_type', 50)
                ->default('purchase')
                ->after('status')
                ->comment('Тип: purchase, quote_request, course_enrollment, digital_download');

            $table->index('order_type');
        });

        // Обновить существующие заказы
        DB::table('orders')
            ->whereNull('order_type')
            ->update(['order_type' => 'purchase']);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['order_type']);
            $table->dropColumn('order_type');
        });
    }
};