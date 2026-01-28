<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Связи parent-child для LMS (Курс → Уроки)
     */
    public function up(): void
    {
        Schema::create('product_relations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parent_id')
                ->constrained('products')
                ->onDelete('cascade')
                ->comment('ID родителя (курс)');

            $table->foreignId('child_id')
                ->constrained('products')
                ->onDelete('cascade')
                ->comment('ID ребёнка (урок)');

            $table->integer('sort_order')
                ->default(0)
                ->comment('Порядок сортировки');

            $table->timestamps();

            // Уникальность пары parent-child
            $table->unique(['parent_id', 'child_id']);
            $table->index(['parent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_relations');
    }
};