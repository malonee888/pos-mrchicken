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
        Schema::create('delivery_slots', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->time('start_time');
    $table->time('end_time');
    $table->decimal('max_capacity_kg', 8, 2);
    $table->decimal('normal_threshold_kg', 8, 2);
    $table->decimal('almost_full_threshold_kg', 8, 2);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_slots');
    }
};
