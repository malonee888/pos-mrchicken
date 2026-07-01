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
        Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->string('transaction_code')->unique();
    $table->foreignId('customer_id')->constrained('customers');
    $table->unsignedBigInteger('delivery_slot_id')->nullable();
    $table->foreignId('user_id')->constrained('users');
    $table->decimal('total_kg', 8, 2)->default(0);
    $table->decimal('total_price', 12, 2)->default(0);
    $table->enum('payment_method', ['lunas', 'hutang', 'dp'])->default('lunas');
    $table->decimal('down_payment', 12, 2)->nullable();
    $table->text('notes')->nullable();
    $table->enum('delivery_status', ['proses', 'dalam_perjalanan', 'terkirim', 'selesai', 'batal'])->default('proses');
    $table->date('transaction_date');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
