<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_code',
        'customer_id',
        'product_id',
        'qty_kg',
        'down_payment',
        'target_delivery_slot_id',
        'queue_position',
        'status',
        'transaction_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function targetSlot()
    {
        return $this->belongsTo(DeliverySlot::class, 'target_delivery_slot_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}