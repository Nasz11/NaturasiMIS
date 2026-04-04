<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
   protected $fillable = [
        'po_number',
        'client_id',
        'client_name',
        'order_date',
        'quantity',
        'unit',
        'status',
        'notes',
        'created_by',
        'confirmed_at',
        'is_archived',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'order_date'   => 'date',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}