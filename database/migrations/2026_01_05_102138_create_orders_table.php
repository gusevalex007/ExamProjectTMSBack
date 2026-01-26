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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained()->onDelete('cascade');
        $table->foreignId('customer_id')->constrained()->onDelete('cascade');
        $table->string('order_number')->unique();
        $table->decimal('total_amount', 10, 2);
        $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
        $table->string('customer_name');
        $table->string('customer_email')->nullable();
        $table->string('customer_phone')->nullable();
        $table->text('shipping_address')->nullable();
        $table->text('notes')->nullable();
        $table->timestamps();

        $table->index('project_id');
        $table->index('customer_id');
        $table->index('order_number');
        $table->index('status');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
