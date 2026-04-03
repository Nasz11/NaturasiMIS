<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = [
        'product_name', 'category', 'quantity',
        'unit', 'reorder_level', 'cost_per_unit',
        'status', 'updated_by', 'is_archived',
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

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'inventory_item_id');
    }

    // Auto-calculate current quantity from movements
    public function computedQuantity()
    {
        $inbound  = $this->movements()->where('type', 'inbound')->sum('quantity');
        $outbound = $this->movements()->where('type', 'outbound')->sum('quantity');
        return $inbound - $outbound;
    }

    // Starting inventory for a given date
    public function startingInventory($date)
    {
        $inbound  = $this->movements()->where('type', 'inbound')->whereDate('movement_date', '<', $date)->sum('quantity');
        $outbound = $this->movements()->where('type', 'outbound')->whereDate('movement_date', '<', $date)->sum('quantity');
        return $inbound - $outbound;
    }

    // Ending inventory for a given date
    public function endingInventory($date)
    {   
        $inbound  = $this->movements()->where('type', 'inbound')->whereDate('movement_date', '<=', $date)->sum('quantity');
        $outbound = $this->movements()->where('type', 'outbound')->whereDate('movement_date', '<=', $date)->sum('quantity');
        return $inbound - $outbound;
    }
}