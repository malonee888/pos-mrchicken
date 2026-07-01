<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySlotCapacity extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_slot_id',
        'date',
        'used_kg',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function deliverySlot()
    {
        return $this->belongsTo(DeliverySlot::class);
    }
}