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
        Schema::create('daily_slot_capacities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('delivery_slot_id')->constrained('delivery_slots');
    $table->date('date');
    $table->decimal('used_kg', 8, 2)->default(0);
    $table->timestamps();

    $table->unique(['delivery_slot_id', 'date']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_slot_capacities');
    }
};
