<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'cheese_product',
        'variant_name',
        'weight_grams',
        'quantity_pieces',
        'total_kg',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}