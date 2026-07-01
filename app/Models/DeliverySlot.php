<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliverySlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'max_capacity_kg',
        'normal_threshold_kg',
        'almost_full_threshold_kg',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function dailyCapacities()
    {
        return $this->hasMany(DailySlotCapacity::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function preOrders()
    {
        return $this->hasMany(PreOrder::class);
    }

    // Helper: ambil/buat kapasitas hari ini untuk slot ini
    public function kapasitasHariIni()
    {
        return $this->dailyCapacities()->firstOrCreate(
            ['date' => today()->format('Y-m-d')],
            ['used_kg' => 0]
        );
    }
}