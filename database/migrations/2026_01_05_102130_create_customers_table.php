<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained()->onDelete('cascade');
        $table->bigInteger('telegram_id')->unique();
        $table->string('name');
        $table->string('username')->nullable();
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->integer('orders_count')->default(0);
        $table->decimal('total_spent', 10, 2)->default(0);
        $table->timestamps();

        $table->index('project_id');
        $table->index('telegram_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
