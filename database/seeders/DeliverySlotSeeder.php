<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliverySlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    \App\Models\DeliverySlot::create([
        'name' => 'Pagi',
        'start_time' => '06:00',
        'end_time' => '12:00',
        'max_capacity_kg' => 60,
        'normal_threshold_kg' => 30,
        'almost_full_threshold_kg' => 45,
        'is_active' => true,
    ]);

    \App\Models\DeliverySlot::create([
        'name' => 'Sore',
        'start_time' => '14:00',
        'end_time' => '18:00',
        'max_capacity_kg' => 60,
        'normal_threshold_kg' => 30,
        'almost_full_threshold_kg' => 45,
        'is_active' => true,
    ]);
}
}
