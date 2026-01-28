<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Добавляем поддержку мультишаблонов:
     * - projects.template_type - тип приложения (shop, catalog, lms, digital_products)
     * - products.type - тип продукта (product, course, lesson, digital_product)
     */
    public function up(): void
    {
        // 1. Добавляем template_type в projects
        Schema::table('projects', function (Blueprint $table) {
            $table->string('template_type', 50)
                ->default('shop')
                ->after('type')
                ->comment('Тип шаблона: shop, catalog, lms, digital_products, portfolio, restaurant');

            $table->index('template_type');
        });

        // 2. Добавляем type в products
        Schema::table('products', function (Blueprint $table) {
            $table->string('type', 50)
                ->default('product')
                ->after('category_id')
                ->comment('Тип продукта: product, digital_product, course, lesson, portfolio_item, menu_item');

            // Составной индекс для быстрых выборок
            $table->index(['project_id', 'type']);
        });

        // 3. Обновляем все существующие записи (обратная совместимость)
        DB::table('projects')
            ->whereNull('template_type')
            ->update(['template_type' => 'shop']);

        DB::table('products')
            ->whereNull('type')
            ->update(['type' => 'product']);
    }

    /**
     * Откат миграции
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'type']);
            $table->dropColumn('type');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['template_type']);
            $table->dropColumn('template_type');
        });
    }
};