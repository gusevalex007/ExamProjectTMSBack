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
    Schema::create('projects', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('name');
        $table->string('category');
        $table->string('bot_token')->nullable();
        $table->enum('status', ['draft', 'customized', 'active'])->default('draft');
        $table->boolean('is_paid')->default(false);
        $table->json('theme_config')->nullable();
        $table->timestamps();

        $table->index('user_id');
        $table->index('status');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
