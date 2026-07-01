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
        Schema::create('pre_orders', function (Blueprint $table) {
    $table->id();
    $table->string('po_code')->unique();
    $table->foreignId('customer_id')->constrained('customers');
    $table->foreignId('product_id')->constrained('products');
    $table->decimal('qty_kg', 8, 2);
    $table->decimal('down_payment', 12, 2)->nullable();
    $table->foreignId('target_delivery_slot_id')->nullable()->constrained('delivery_slots');
    $table->integer('queue_position');
    $table->enum('status', ['menunggu', 'dialokasikan', 'batal'])->default('menunggu');
    $table->foreignId('transaction_id')->nullable()->constrained('transactions');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_orders');
    }
};
