<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class ProductionBatch extends Model
{
    use HasFactory;
    protected $fillable = [
        'batch_number', 'product_type', 'quantity',
        'production_date', 'status', 'remarks', 'staff_id', 'is_archived',
    ];
    protected $casts = ['production_date' => 'date'];
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
