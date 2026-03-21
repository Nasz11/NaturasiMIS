<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProductionBatch extends Model
{
    protected $fillable = [
        'batch_number', 'product_type', 'quantity',
        'production_date', 'status', 'remarks', 'staff_id',
    ];

    protected $casts = ['production_date' => 'date'];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
