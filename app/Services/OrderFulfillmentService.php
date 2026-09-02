<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderFulfillmentService
{
    public function __construct(
        protected BatchCalculatorService $batchCalculator,
        protected InventoryLedgerService $ledger
    ) {}

    /**
     * Finds an inventory item by ingredient name with fuzzy/alias fallback.
     */
    public function findInventoryItemByName(string $ingredientName): ?InventoryItem
    {
        $ingredientName = trim($ingredientName);
        $normalizedName = strtolower($ingredientName);

        $inventoryItem = InventoryItem::whereRaw(
            'LOWER(product_name) = ?',
            [$normalizedName]
        )
        ->orderByDesc('quantity')
        ->orderByDesc('id')
        ->first();

        if (!$inventoryItem && $normalizedName === 'iodized salt') {
            $inventoryItem = InventoryItem::whereRaw(
                'LOWER(product_name) = ?',
                ['salt']
            )
            ->orderByDesc('quantity')
            ->orderByDesc('id')
            ->first();
        }

        if (!$inventoryItem) {
            $inventoryItem = InventoryItem::whereRaw(
                'LOWER(product_name) LIKE ?',
                ['%' . $normalizedName . '%']
            )
            ->orderByDesc('quantity')
            ->orderByDesc('id')
            ->first();
        }

        return $inventoryItem;
    }

    /**
     * Calculates required raw ingredients from a list of ordered items.
     */
    public function calculateIngredients(array $items): array
    {
        $deductions       = [];
        $totalMilkNeeded  = 0;
        $totalCreamNeeded = 0;
        $totalSaltNeeded  = 0;
        $batchAdjustments = [];

        foreach ($items as $item) {
            $variant = isset($item['variant_id']) ? ProductVariant::find($item['variant_id']) : null;
            $quantityPcs = (float) ($item['quantity_pcs'] ?? $item['quantity_pieces'] ?? 1);
            $rawKg = ($variant ? $variant->weight_grams / 1000 : 0) * $quantityPcs;
            $totalKg = isset($item['total_kg']) ? (float)$item['total_kg'] : $rawKg;
            $productName = $item['product'] ?? $item['cheese_product'];

            $recipes = Recipe::where('cheese_product', $productName)->get();

            foreach ($recipes as $recipe) {
                $isBatchProduct = in_array($productName, ['Burrata', 'Stracciatella']);
                if (in_array($recipe->ingredient_name, ['Fresh Milk', 'Cream', 'Iodized Salt']) && $isBatchProduct) {
                    continue;
                }

                $needed = $recipe->quantity_needed * $totalKg;
                $key = $recipe->ingredient_name;
                $deductions[$key] = ($deductions[$key] ?? 0) + $needed;
            }

            if (in_array($productName, ['Burrata', 'Stracciatella'])) {
                if ($totalKg <= 22.5) {
                    $normalizedKg = $this->batchCalculator->normalizeToBatchSize($totalKg);
                    if (abs($normalizedKg - $totalKg) > 0.0001) {
                        $batchAdjustments[] = [
                            'product'    => $productName . ' ' . ($variant ? $variant->variant_name : ''),
                            'requested'  => round($totalKg, 4),
                            'adjusted'   => $normalizedKg,
                            'difference' => round($normalizedKg - $totalKg, 4),
                            'percentage' => round((($normalizedKg - $totalKg) / $totalKg) * 100, 2),
                        ];
                    }
                }

                $totals            = $this->batchCalculator->computeProductionTotals($totalKg, $productName);
                $totalMilkNeeded  += $totals['totalMilk'];
                $totalCreamNeeded += $totals['totalCream'];
                $totalSaltNeeded  += $totals['totalSalt'];
            }
        }

        $deductions['Fresh Milk']   = ($deductions['Fresh Milk'] ?? 0) + $totalMilkNeeded;
        $deductions['Cream']        = ($deductions['Cream'] ?? 0) + ($totalCreamNeeded * 0.0625);
        $deductions['Iodized Salt'] = ($deductions['Iodized Salt'] ?? 0) + ($totalSaltNeeded * 0.006);
        $deductions                 = array_filter($deductions, fn ($qty) => $qty > 0);

        return [
            'deductions'       => $deductions,
            'totalMilk'        => $totalMilkNeeded,
            'totalCream'       => $totalCreamNeeded,
            'totalSalt'        => $totalSaltNeeded,
            'batchAdjustments' => $batchAdjustments,
        ];
    }

    /**
     * Generates a preview JSON response array for real-time order review.
     */
    public function generatePreview(array $items): array
    {
        $preview = [];
        $calculation = $this->calculateIngredients($items);

        foreach ($items as $item) {
            $variant = isset($item['variant_id']) ? ProductVariant::find($item['variant_id']) : null;
            $quantityPcs = (float) ($item['quantity_pcs'] ?? 1);
            $rawKg = ($variant ? $variant->weight_grams / 1000 : 0) * $quantityPcs;
            $totalKg = isset($item['total_kg']) ? (float)$item['total_kg'] : $rawKg;
            $productName = $item['product'];

            $recipes = Recipe::where('cheese_product', $productName)->get();

            foreach ($recipes as $recipe) {
                $isBatchProduct = in_array($productName, ['Burrata', 'Stracciatella']);
                if (in_array($recipe->ingredient_name, ['Fresh Milk', 'Cream', 'Iodized Salt']) && $isBatchProduct) {
                    continue;
                }

                $needed = $recipe->quantity_needed * $totalKg;
                $key = $recipe->ingredient_name;

                if (isset($preview[$key])) {
                    $preview[$key]['needed'] += $needed;
                } else {
                    $inventoryItem = $this->findInventoryItemByName($recipe->ingredient_name);
                    $preview[$key] = [
                        'ingredient' => $recipe->ingredient_name,
                        'needed'     => $needed,
                        'available'  => $inventoryItem ? $inventoryItem->quantity : 0,
                        'unit'       => $recipe->unit,
                    ];
                }
            }
        }

        if ($calculation['totalMilk'] > 0) {
            $inventoryItem = $this->findInventoryItemByName('Fresh Milk');
            $preview['Fresh Milk'] = [
                'ingredient' => 'Fresh Milk',
                'needed'     => $calculation['totalMilk'],
                'available'  => $inventoryItem ? $inventoryItem->quantity : 0,
                'unit'       => 'L',
            ];
        }

        if ($calculation['totalCream'] > 0) {
            $inventoryItem = $this->findInventoryItemByName('Cream');
            $availableLiters = $inventoryItem ? $inventoryItem->quantity : 0;
            $preview['Cream'] = [
                'ingredient' => 'Cream',
                'needed'     => $calculation['totalCream'],
                'available'  => floor($availableLiters / 0.0625),
                'unit'       => 'pcs',
            ];
        }

        if ($calculation['totalSalt'] > 0) {
            $inventoryItem = $this->findInventoryItemByName('Iodized Salt');
            $availableKg = $inventoryItem ? $inventoryItem->quantity : 0;
            $preview['Iodized Salt'] = [
                'ingredient' => 'Iodized Salt',
                'needed'     => $calculation['totalSalt'],
                'available'  => round($availableKg / 0.006, 2),
                'unit'       => 'Scoops',
            ];
        }

        $insufficient = collect($preview)->filter(fn($i) => $i['needed'] > $i['available'])->count();

        return [
            'preview'          => array_values($preview),
            'insufficient'     => $insufficient,
            'batchAdjustments' => $calculation['batchAdjustments'],
        ];
    }

    /**
     * Confirms an order directly, deducting inventory inside an atomic transaction.
     */
    public function confirmOrder(array $orderData, array $items, int $userId): Order
    {
        $calculation = $this->calculateIngredients($items);
        $deductions = $calculation['deductions'];

        // Pre-check availability
        foreach ($deductions as $ingredientName => $needed) {
            $inventoryItem = $this->findInventoryItemByName($ingredientName);
            if (!$inventoryItem || $inventoryItem->quantity < $needed) {
                throw ValidationException::withMessages([
                    'insufficient' => "Not enough stock for: {$ingredientName}. Available: " .
                        ($inventoryItem ? $inventoryItem->quantity : 0) . ", Needed: {$needed}",
                ]);
            }
        }

        $poNumber = 'PO-' . now()->format('Y') . '-' . strtoupper(substr(uniqid(), -5));

        $movements = [];
        foreach ($deductions as $ingredientName => $needed) {
            $inventoryItem = $this->findInventoryItemByName($ingredientName);
            $movements[] = [
                'inventory_item_id' => $inventoryItem->id,
                'type'              => 'outbound',
                'quantity'          => $needed,
                'reference'         => $poNumber,
                'remarks'           => "Deducted for {$poNumber} — {$orderData['client_name']}",
                'recorded_by'       => $userId,
                'movement_date'     => now(),
                'expiry_date'       => null,
            ];
        }

        $order = DB::transaction(function () use ($movements, $poNumber, $orderData, $items, $userId) {
            $this->ledger->record($movements);

            $order = Order::create([
                'po_number'    => $poNumber,
                'client_id'    => $orderData['client_id'] ?? null,
                'client_name'  => $orderData['client_name'],
                'order_date'   => $orderData['order_date'],
                'quantity'     => 0,
                'unit'         => 'kg',
                'status'       => 'Confirmed',
                'notes'        => $orderData['notes'] ?? null,
                'created_by'   => $userId,
                'confirmed_at' => now(),
            ]);

            $totalKgAll = 0;
            foreach ($items as $item) {
                $variant     = ProductVariant::findOrFail($item['variant_id']);
                $quantityPcs = (float) $item['quantity_pcs'];
                $totalKg     = ($variant->weight_grams / 1000) * $quantityPcs;
                $totalKgAll += $totalKg;

                OrderItem::create([
                    'order_id'        => $order->id,
                    'cheese_product'  => $item['product'],
                    'variant_name'    => $variant->variant_name,
                    'weight_grams'    => $variant->weight_grams,
                    'quantity_pieces' => $quantityPcs,
                    'total_kg'        => $totalKg,
                ]);
            }

            $order->update(['quantity' => $totalKgAll]);

            return $order;
        });

        ActivityLog::record('Orders', 'Confirmed Order', "Order {$poNumber} confirmed for {$orderData['client_name']}.");

        return $order;
    }

    /**
     * Handles order status transitions, including stock deduction or restoration.
     */
    public function updateStatus(Order $order, string $newStatus, int $userId): void
    {
        $validTransitions = [
            'Pending'   => ['Confirmed', 'Cancelled'],
            'Confirmed' => ['Completed', 'Cancelled'],
            'Completed' => [],
            'Cancelled' => [],
        ];

        if (!in_array($newStatus, $validTransitions[$order->status] ?? [])) {
            throw ValidationException::withMessages([
                'error' => "Invalid status transition from {$order->status} to {$newStatus}."
            ]);
        }

        $itemsArray = $order->items->map(fn($item) => [
            'cheese_product'  => $item->cheese_product,
            'quantity_pieces' => $item->quantity_pieces,
            'total_kg'        => $item->total_kg,
        ])->toArray();

        $calculation = $this->calculateIngredients($itemsArray);
        $movements = [];

        if ($newStatus === 'Confirmed' && $order->status !== 'Confirmed') {
            $deductions = $calculation['deductions'];

            foreach ($deductions as $ingredientName => $needed) {
                $inventoryItem = $this->findInventoryItemByName($ingredientName);
                if (!$inventoryItem || $inventoryItem->quantity < $needed) {
                    throw ValidationException::withMessages([
                        'insufficient' => "Not enough stock for: {$ingredientName}."
                    ]);
                }

                $movements[] = [
                    'inventory_item_id' => $inventoryItem->id,
                    'type'              => 'outbound',
                    'quantity'          => $needed,
                    'reference'         => $order->po_number,
                    'remarks'           => "Deducted for {$order->po_number} — {$order->client_name}",
                    'recorded_by'       => $userId,
                    'movement_date'     => now(),
                    'expiry_date'       => null,
                ];
            }
        }

        if ($newStatus === 'Cancelled' && $order->status === 'Confirmed') {
            $restorations = $calculation['deductions'];

            foreach ($restorations as $ingredientName => $amount) {
                $inventoryItem = $this->findInventoryItemByName($ingredientName);
                if ($inventoryItem) {
                    $movements[] = [
                        'inventory_item_id' => $inventoryItem->id,
                        'type'              => 'inbound',
                        'quantity'          => $amount,
                        'reference'         => $order->po_number,
                        'remarks'           => "Restored — {$order->po_number} cancelled",
                        'recorded_by'       => $userId,
                        'movement_date'     => now(),
                        'expiry_date'       => null,
                    ];
                }
            }
        }

        DB::transaction(function () use ($movements, $order, $newStatus) {
            if (!empty($movements)) {
                $this->ledger->record($movements);
            }

            $order->status       = $newStatus;
            $order->confirmed_at = $newStatus === 'Confirmed' ? now() : ($newStatus === 'Cancelled' ? null : $order->confirmed_at);
            $order->save();
        });

        ActivityLog::record('Orders', 'Updated Order Status', "Order {$order->po_number} marked as {$newStatus}.");
    }
}
