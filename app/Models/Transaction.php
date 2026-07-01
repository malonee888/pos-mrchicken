<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'customer_id',
        'delivery_slot_id',
        'user_id',
        'total_kg',
        'total_price',
        'payment_method',
        'down_payment',
        'notes',
        'delivery_status',
        'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function debt()
    {
        return $this->hasOne(Debt::class);
    }

    public function deliverySlot()
    {
        return $this->belongsTo(DeliverySlot::class);
    }
}