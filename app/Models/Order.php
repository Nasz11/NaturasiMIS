<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
    'po_number',
    'cheese_product',
    'quantity',
    'unit',
    'status',
    'notes',
    'created_by',
    'confirmed_at',
];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}