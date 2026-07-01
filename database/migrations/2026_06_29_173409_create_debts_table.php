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
        Schema::create('debts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('transaction_id')->constrained('transactions');
    $table->foreignId('customer_id')->constrained('customers');
    $table->decimal('initial_amount', 12, 2);
    $table->decimal('paid_amount', 12, 2)->default(0);
    $table->enum('status', ['belum_lunas', 'cicilan', 'lunas'])->default('belum_lunas');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
