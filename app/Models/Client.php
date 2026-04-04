<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'is_archived',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}