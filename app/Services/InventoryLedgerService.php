<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Validation\ValidationException;

class InventoryLedgerService
{
    /**
     * Records stock movements and synchronizes the cached item quantity.
     *
     * The caller must execute this method inside a database transaction.
     * Each entry requires inventory_item_id, type, quantity, reference,
     * remarks, recorded_by, and movement_date.
     */
    public function record(array $entries): void
    {
        usort($entries, fn (array $left, array $right) => $left['inventory_item_id'] <=> $right['inventory_item_id']);

        foreach ($entries as $entry) {
            $quantity = (float) $entry['quantity'];

            if ($quantity <= 0) {
                continue;
            }

            $item = InventoryItem::query()
                ->lockForUpdate()
                ->findOrFail($entry['inventory_item_id']);

            $currentQuantity = (float) InventoryMovement::query()
                ->where('inventory_item_id', $item->id)
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'inbound' THEN quantity ELSE -quantity END), 0) as quantity")
                ->value('quantity');

            if ($entry['type'] === 'outbound' && $currentQuantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Cannot record outbound for {$item->product_name}: insufficient stock.",
                ]);
            }

            InventoryMovement::create($entry);

            $item->quantity = $entry['type'] === 'inbound'
                ? $currentQuantity + $quantity
                : $currentQuantity - $quantity;
            $item->save();
        }
    }
}
