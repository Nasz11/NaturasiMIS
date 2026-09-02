<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'cheese_product',
        'ingredient_name',
        'quantity_needed',
        'unit',
    ];
}