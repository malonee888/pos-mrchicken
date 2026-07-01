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
        Schema::create('debt_payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('debt_id')->constrained('debts');
    $table->decimal('amount', 12, 2);
    $table->enum('payment_method', ['transfer', 'tunai', 'cod']);
    $table->date('payment_date');
    $table->foreignId('user_id')->constrained('users');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
    }
};
