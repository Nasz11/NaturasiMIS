<?php
    // InventoryItem.php
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;

    class InventoryItem extends Model
    {
        protected $fillable = [
            'product_name', 'category', 'quantity',
            'unit', 'reorder_level', 'status', 'updated_by',
        ];

        protected static function booted()
        {
            static::saving(function ($item) {
                if ($item->quantity <= 0) {
                    $item->status = 'Out of Stock';
                } elseif ($item->quantity <= $item->reorder_level) {
                    $item->status = 'Low Stock';
                } else {
                    $item->status = 'In Stock';
                }
            });
        }

        public function updatedBy()
        {
            return $this->belongsTo(User::class, 'updated_by');
        }
    }
