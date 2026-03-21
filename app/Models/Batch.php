<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $fillable = [
        'batch_id', 'cheese_type', 'quantity',
        'start_date', 'completion_date', 'status', 'staff_id', 'remarks',
    ];

    protected $casts = [
        'start_date'      => 'date',
        'completion_date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
