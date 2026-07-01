<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained('products');
    $table->enum('type', ['masuk', 'keluar']);
    $table->decimal('quantity_kg', 8, 2);
    $table->string('reference_type')->nullable();
    $table->unsignedBigInteger('reference_id')->nullable();
    $table->string('note')->nullable();
    $table->foreignId('user_id')->constrained('users');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
