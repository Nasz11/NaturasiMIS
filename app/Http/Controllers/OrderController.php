<?php

        namespace App\Http\Controllers;

        use App\Models\ActivityLog;
        use App\Models\Client;
        use App\Models\InventoryItem;
        use App\Models\Order;
        use App\Models\OrderItem;
        use App\Models\ProductVariant;
        use App\Models\Recipe;
        use Illuminate\Http\Request;

        class OrderController extends Controller
        {
            private function normalizeToBatchSize($kg)
            {
                $sizes = [
                    1.25, 2.5, 3.75, 5, 6.25, 7.5, 8.75, 10,
                    11.25, 12.5, 13.75, 15, 16.25, 17.5,
                    18.75, 20, 21.25, 22.5
                ];

                // Only normalize if below or equal to max batch size
                if ($kg <= 22.5) {
                    foreach ($sizes as $size) {
                        if ($kg <= $size + 0.0001) {
                            return $size;
                        }
                    }
                }

                // Large orders — let splitIntoBatches handle it
                return $kg;
            }

            private function splitIntoBatches($totalYield)
            {
                $batchSizes = [
                    22.5, 21.25, 20, 18.75, 17.5, 16.25, 15,
                    13.75, 12.5, 11.25, 10, 8.75, 7.5,
                    6.25, 5, 3.75, 2.5, 1.25
                ];

                $batches = [];

                while ($totalYield > 0.0001) {
                    foreach ($batchSizes as $size) {
                        if ($totalYield >= $size - 0.0001) {
                            $batches[] = $size;
                            $totalYield -= $size;
                            break;
                        }
                    }
                }

                return $batches;
            }

            private function getMilkRequired($yieldKg)
        {
            $map = [
                '1.25'  => 0,
                '2.50'  => 0,
                '3.75'  => 0,
                '5.00'  => 1,
                '6.25'  => 1,
                '7.50'  => 1,
                '8.75'  => 1,
                '10.00' => 1,
                '11.25' => 1,
                '12.50' => 1,
                '13.75' => 2,
                '15.00' => 2,
                '16.25' => 2,
                '17.50' => 2,
                '18.75' => 2,
                '20.00' => 2,
                '21.25' => 2,
                '22.50' => 3,
            ];

            $key = number_format($yieldKg, 2, '.', '');

            return $map[$key] ?? 0;
        }

            private function findInventoryItemByName($ingredientName)
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

            private function getCreamRequired($yieldKg)
            {
                $map = [
                    '1.25'  => 3,  '2.5'   => 6,  '3.75'  => 5,
                    '5'     => 8,  '6.25'  => 11, '7.5'   => 14,
                    '8.75'  => 17, '10'    => 20, '11.25' => 23,
                    '12.5'  => 26, '13.75' => 25, '15'    => 28,
                    '16.25' => 31, '17.5'  => 34, '18.75' => 37,
                    '20'    => 40, '21.25' => 43, '22.5'  => 42,
                ];

                foreach ($map as $yield => $pcs) {
                    if (abs($yieldKg - (float)$yield) < 0.0001) {
                        return $pcs;
                    }
                }

                // Should never reach here after normalization/splitting
                return 0;
            }

        private function getSaltRequired($yieldKg)
        {
            $map = [
                '1.25'  => 1,    '2.50'  => 2.5,  '3.75'  => 3.25,
                '5.00'  => 4,    '6.25'  => 4.75, '7.50'  => 5.5,
                '8.75'  => 6.25, '10.00' => 7,    '11.25' => 7.75,
                '12.50' => 8.5,  '13.75' => 9.25, '15.00' => 10.5,
                '16.25' => 11,   '17.50' => 12,   '18.75' => 13,
                '20.00' => 13.5, '21.25' => 14.25,'22.50' => 15.5,
            ];

            $key = number_format($yieldKg, 2, '.', '');

            if (isset($map[$key])) {
                return $map[$key];
            }

            return 0;
        }

        private function computeProductionTotals($totalKg, $product)
            {
                $totalCream = 0;
                $totalMilk  = 0;
                $totalSalt  = 0;

                if (!in_array($product, ['Burrata', 'Stracciatella'])) {
                    return compact('totalCream', 'totalMilk', 'totalSalt');
                }

                // Always normalize total to valid 1.25kg multiple first, then split
                $normalizedTotal = ceil($totalKg / 1.25) * 1.25;
                $batches         = $this->splitIntoBatches($normalizedTotal);

                foreach ($batches as $batch) {
                    $totalCream += $this->getCreamRequired($batch);
                    $totalMilk  += $this->getMilkRequired($batch);
                    $totalSalt  += $this->getSaltRequired($batch);
                }

                return compact('totalCream', 'totalMilk', 'totalSalt');
            }

            public function index(Request $request)
            {
                $search    = $request->get('search');
                $activeTab = $request->get('tab', 'orders');

                $orders = Order::with(['createdBy', 'client', 'items'])
                    ->where('is_archived', false)
                    ->when($search, fn($q) => $q->where('po_number', 'like', "%{$search}%")
                        ->orWhere('client_name', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%"))
                    ->latest()
                    ->paginate(10)
                    ->withQueryString();

                $archivedOrders  = Order::with(['createdBy', 'client', 'items'])
                    ->where('is_archived', true)
                    ->latest()
                    ->get();
                $clients         = Client::where('is_archived', false)->orderBy('name')->get();
                $archivedClients = Client::where('is_archived', true)->orderBy('name')->get();
                $variants        = ProductVariant::orderBy('cheese_product')->orderBy('weight_grams')->get()->groupBy('cheese_product');

                return view('orders.index', compact('orders', 'archivedOrders', 'clients', 'archivedClients', 'variants', 'search', 'activeTab'));
            }

            public function preview(Request $request)
            {
                $request->validate([
                    'items'                => 'required|array|min:1',
                    'items.*.product'      => 'required|string',
                    'items.*.variant_id'   => 'required|numeric',
                    'items.*.quantity_pcs' => 'required|numeric|min:0.001',
                ]);

                $preview          = [];
                $totalMilkNeeded  = 0;
                $totalCreamNeeded = 0;
                $totalSaltNeeded  = 0;
                $batchAdjustments = [];

                foreach ($request->items as $item) {
                    $variant = ProductVariant::find($item['variant_id']);
                    $rawKg   = ($variant ? $variant->weight_grams / 1000 : 0) * $item['quantity_pcs'];
                    $totalKg = isset($item['total_kg']) ? $item['total_kg'] : $rawKg;
                    $recipes = Recipe::where('cheese_product', $item['product'])->get();

                    foreach ($recipes as $recipe) {
                        $isBatchProduct = in_array($item['product'], ['Burrata', 'Stracciatella']);
                        if (in_array($recipe->ingredient_name, ['Fresh Milk', 'Cream', 'Iodized Salt']) && $isBatchProduct) continue;

                        $needed = $recipe->quantity_needed * $totalKg;
                        $key    = $recipe->ingredient_name;

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

                    if (in_array($item['product'], ['Burrata', 'Stracciatella'])) {
                        // Track batch adjustment warning
                        if ($totalKg <= 22.5) {
                            $normalizedKg = $this->normalizeToBatchSize($totalKg);
                            if (abs($normalizedKg - $totalKg) > 0.0001) {
                                $batchAdjustments[] = [
                                    'product'    => $item['product'] . ' ' . ($variant ? $variant->variant_name : ''),
                                    'requested'  => round($totalKg, 4),
                                    'adjusted'   => $normalizedKg,
                                    'difference' => round($normalizedKg - $totalKg, 4),
                                    'percentage' => round((($normalizedKg - $totalKg) / $totalKg) * 100, 2),
                                ];
                            }
                        }

                        $totals            = $this->computeProductionTotals($totalKg, $item['product']);
                        $totalMilkNeeded  += $totals['totalMilk'];
                        $totalCreamNeeded += $totals['totalCream'];
                        $totalSaltNeeded  += $totals['totalSalt'];
                    }
                }

                // Add Fresh Milk to preview
                if ($totalMilkNeeded > 0) {
                    $inventoryItem         = $this->findInventoryItemByName('Fresh Milk');
                    $preview['Fresh Milk'] = [
                        'ingredient' => 'Fresh Milk',
                        'needed'     => $totalMilkNeeded,
                        'available'  => $inventoryItem ? $inventoryItem->quantity : 0,
                        'unit'       => 'L',
                    ];
                }

                // Add Cream to preview (display in pcs)
                if ($totalCreamNeeded > 0) {
                    $inventoryItem    = $this->findInventoryItemByName('Cream');
                    $availableLiters  = $inventoryItem ? $inventoryItem->quantity : 0;
                    $preview['Cream'] = [
                        'ingredient' => 'Cream',
                        'needed'     => $totalCreamNeeded,
                        'available'  => floor($availableLiters / 0.0625),
                        'unit'       => 'pcs',
                    ];
                }

                // Add Salt to preview (display in scoops)
                if ($totalSaltNeeded > 0) {
                    $inventoryItem           = $this->findInventoryItemByName('Iodized Salt');
                    $availableKg             = $inventoryItem ? $inventoryItem->quantity : 0;
                    $preview['Iodized Salt'] = [
                        'ingredient' => 'Iodized Salt',
                        'needed'     => $totalSaltNeeded,
                        'available'  => round($availableKg / 0.006, 2),
                        'unit'       => 'Scoops',
                    ];
                }

                $insufficient = collect($preview)->filter(fn($i) => $i['needed'] > $i['available'])->count();

                return response()->json([
                    'preview'          => array_values($preview),
                    'insufficient'     => $insufficient,
                    'batchAdjustments' => $batchAdjustments,
                ]);
            }

            public function confirm(Request $request)
            {
                $request->validate([
                    'client_id'            => 'nullable|integer',
                    'client_name'          => 'required|string',
                    'order_date'           => 'required|date',
                    'notes'                => 'nullable|string',
                    'items'                => 'required|array|min:1',
                    'items.*.product'      => 'required|string',
                    'items.*.variant_id'   => 'required|integer',
                    'items.*.quantity_pcs' => 'required|numeric|min:1',
                ]);

                $deductions       = [];
                $totalMilkNeeded  = 0;
                $totalCreamNeeded = 0;
                $totalSaltNeeded  = 0;

                foreach ($request->items as $item) {
                    $variant = ProductVariant::findOrFail($item['variant_id']);
                    $totalKg = ($variant->weight_grams / 1000) * $item['quantity_pcs'];
                    $recipes = Recipe::where('cheese_product', $item['product'])->get();

                    foreach ($recipes as $recipe) {
                        $isBatchProduct = in_array($item['product'], ['Burrata', 'Stracciatella']);
                        if (in_array($recipe->ingredient_name, ['Fresh Milk', 'Cream', 'Iodized Salt']) && $isBatchProduct) continue;

                        $needed           = $recipe->quantity_needed * $totalKg;
                        $key              = $recipe->ingredient_name;
                        $deductions[$key] = ($deductions[$key] ?? 0) + $needed;
                    }

                    $totals            = $this->computeProductionTotals($totalKg, $item['product']);
                    $totalMilkNeeded  += $totals['totalMilk'];
                    $totalCreamNeeded += $totals['totalCream'];
                    $totalSaltNeeded  += $totals['totalSalt'];
                }

                $deductions['Fresh Milk']   = ($deductions['Fresh Milk'] ?? 0) + $totalMilkNeeded;
                $deductions['Cream']        = ($deductions['Cream'] ?? 0) + ($totalCreamNeeded * 0.0625);
                $deductions['Iodized Salt'] = ($deductions['Iodized Salt'] ?? 0) + ($totalSaltNeeded * 0.006);

                // Check availability
                foreach ($deductions as $ingredientName => $needed) {
                    $inventoryItem = $this->findInventoryItemByName($ingredientName);
                    if (!$inventoryItem || $inventoryItem->quantity < $needed) {
                        return back()->withErrors([
                            'insufficient' => "Not enough stock for: {$ingredientName}. Available: " .
                                ($inventoryItem ? $inventoryItem->quantity : 0) . ", Needed: {$needed}",
                        ])->with('tab', 'orders');
                    }
                }

                // Deduct inventory
                foreach ($deductions as $ingredientName => $needed) {
                    $inventoryItem           = $this->findInventoryItemByName($ingredientName);
                    $inventoryItem->quantity -= $needed;
                    $inventoryItem->save();
                }

                $poNumber = 'PO-' . now()->format('Y') . '-' . strtoupper(substr(uniqid(), -5));

                $order = Order::create([
                    'po_number'    => $poNumber,
                    'client_id'    => $request->client_id ?? null,
                    'client_name'  => $request->client_name,
                    'order_date'   => $request->order_date,
                    'quantity'     => 0,
                    'unit'         => 'kg',
                    'status'       => 'Confirmed',
                    'notes'        => $request->notes ?? null,
                    'created_by'   => auth()->id(),
                    'confirmed_at' => now(),
                ]);

                $totalKgAll = 0;
                foreach ($request->items as $item) {
                    $variant     = ProductVariant::findOrFail($item['variant_id']);
                    $totalKg     = ($variant->weight_grams / 1000) * $item['quantity_pcs'];
                    $totalKgAll += $totalKg;

                    OrderItem::create([
                        'order_id'        => $order->id,
                        'cheese_product'  => $item['product'],
                        'variant_name'    => $variant->variant_name,
                        'weight_grams'    => $variant->weight_grams,
                        'quantity_pieces' => $item['quantity_pcs'],
                        'total_kg'        => $totalKg,
                    ]);
                }

                $order->update(['quantity' => $totalKgAll]);

                ActivityLog::record('Orders', 'Confirmed Order', "Order {$poNumber} confirmed for {$request->client_name}.");

                return back()->with('success', "Order {$poNumber} confirmed! Inventory has been updated.");
            }

            public function update(Request $request, Order $order)
            {
                if ($order->status !== 'Pending') {
                    return back()->withErrors(['error' => 'Only pending orders can be edited.']);
                }

                $request->validate([
                    'client_id'            => 'nullable|integer',
                    'client_name'          => 'required|string',
                    'order_date'           => 'required|date',
                    'notes'                => 'nullable|string',
                    'items'                => 'required|array|min:1',
                    'items.*.product'      => 'required|string',
                    'items.*.variant_id'   => 'required|integer',
                    'items.*.quantity_pcs' => 'required|numeric|min:1',
                ]);

                $order->update([
                    'client_id'   => $request->client_id ?? null,
                    'client_name' => $request->client_name,
                    'order_date'  => $request->order_date,
                    'notes'       => $request->notes ?? null,
                ]);

                $order->items()->delete();

                $totalKgAll = 0;
                foreach ($request->items as $item) {
                    $variant     = ProductVariant::findOrFail($item['variant_id']);
                    $totalKg     = ($variant->weight_grams / 1000) * $item['quantity_pcs'];
                    $totalKgAll += $totalKg;

                    OrderItem::create([
                        'order_id'        => $order->id,
                        'cheese_product'  => $item['product'],
                        'variant_name'    => $variant->variant_name,
                        'weight_grams'    => $variant->weight_grams,
                        'quantity_pieces' => $item['quantity_pcs'],
                        'total_kg'        => $totalKg,
                    ]);
                }

                $order->update(['quantity' => $totalKgAll]);

                ActivityLog::record('Orders', 'Updated Order', "Order {$order->po_number} updated.");

                return back()->with('success', "Order {$order->po_number} updated successfully!");
            }

            public function updateStatus(Request $request, Order $order)
        {
            $request->validate(['status' => 'required|in:Confirmed,Cancelled,Completed']);

            // ✅ VALID STATUS FLOW (FIXED)
            $validTransitions = [
                'Pending'   => ['Confirmed', 'Cancelled'],
                'Confirmed' => ['Completed', 'Cancelled'],
                'Completed' => [],
                'Cancelled' => [],
            ];

            if (!in_array($request->status, $validTransitions[$order->status] ?? [])) {
                return back()->withErrors([
                    'error' => "Invalid status transition from {$order->status} to {$request->status}."
                ]);
            }

            // ✅ DEDUCT INVENTORY WHEN CONFIRMED
            if ($request->status === 'Confirmed' && $order->status !== 'Confirmed') {
                $deductions       = [];
                $totalMilkNeeded  = 0;
                $totalCreamNeeded = 0;
                $totalSaltNeeded  = 0;

                foreach ($order->items as $item) {
                    $recipes = Recipe::where('cheese_product', $item->cheese_product)->get();

                    foreach ($recipes as $recipe) {
                        $isBatchProduct = in_array($item->cheese_product, ['Burrata', 'Stracciatella']);
                        if (in_array($recipe->ingredient_name, ['Fresh Milk', 'Cream', 'Iodized Salt']) && $isBatchProduct) continue;

                        $needed = $recipe->quantity_needed * $item->total_kg;
                        $deductions[$recipe->ingredient_name] = ($deductions[$recipe->ingredient_name] ?? 0) + $needed;
                    }

                    $totals            = $this->computeProductionTotals($item->total_kg, $item->cheese_product);
                    $totalMilkNeeded  += $totals['totalMilk'];
                    $totalCreamNeeded += $totals['totalCream'];
                    $totalSaltNeeded  += $totals['totalSalt'];
                }

                $deductions['Fresh Milk']   = ($deductions['Fresh Milk'] ?? 0) + $totalMilkNeeded;
                $deductions['Cream']        = ($deductions['Cream'] ?? 0) + ($totalCreamNeeded * 0.0625);
                $deductions['Iodized Salt'] = ($deductions['Iodized Salt'] ?? 0) + ($totalSaltNeeded * 0.006);

                foreach ($deductions as $ingredientName => $needed) {
                    $inventoryItem = $this->findInventoryItemByName($ingredientName);

                    if (!$inventoryItem || $inventoryItem->quantity < $needed) {
                        return back()->withErrors([
                            'insufficient' => "Not enough stock for: {$ingredientName}."
                        ]);
                    }
                }

                foreach ($deductions as $ingredientName => $needed) {
                    $inventoryItem = $this->findInventoryItemByName($ingredientName);

                    $inventoryItem->quantity -= $needed;
                    $inventoryItem->save();

                    \App\Models\InventoryMovement::create([
                        'inventory_item_id' => $inventoryItem->id,
                        'type'              => 'outbound',
                        'quantity'          => $needed,
                        'reference'         => $order->po_number,
                        'remarks'           => 'Auto-deducted from confirmed order',
                        'recorded_by'       => auth()->id(),
                        'movement_date'     => now(),
                    ]);
                }
            }

            // ✅ RESTORE INVENTORY WHEN CANCELLED
            if ($request->status === 'Cancelled' && $order->status === 'Confirmed') {
                $restorations       = [];
                $totalMilkRestore   = 0;
                $totalCreamRestore  = 0;
                $totalSaltRestore   = 0;

                foreach ($order->items as $item) {
                    $recipes = Recipe::where('cheese_product', $item->cheese_product)->get();

                    foreach ($recipes as $recipe) {
                        $isBatchProduct = in_array($item->cheese_product, ['Burrata', 'Stracciatella']);
                        if (in_array($recipe->ingredient_name, ['Fresh Milk', 'Cream', 'Iodized Salt']) && $isBatchProduct) continue;

                        $amount = $recipe->quantity_needed * $item->total_kg;
                        $restorations[$recipe->ingredient_name] = ($restorations[$recipe->ingredient_name] ?? 0) + $amount;
                    }

                    $totals = $this->computeProductionTotals($item->total_kg, $item->cheese_product);
                    $totalMilkRestore  += $totals['totalMilk'];
                    $totalCreamRestore += $totals['totalCream'];
                    $totalSaltRestore  += $totals['totalSalt'];
                }

                $restorations['Fresh Milk']   = ($restorations['Fresh Milk'] ?? 0) + $totalMilkRestore;
                $restorations['Cream']        = ($restorations['Cream'] ?? 0) + ($totalCreamRestore * 0.0625);
                $restorations['Iodized Salt'] = ($restorations['Iodized Salt'] ?? 0) + ($totalSaltRestore * 0.006);

                foreach ($restorations as $ingredientName => $amount) {
                    $inventoryItem = $this->findInventoryItemByName($ingredientName);

                    if ($inventoryItem) {
                        $inventoryItem->quantity += $amount;
                        $inventoryItem->save();

                        \App\Models\InventoryMovement::create([
                            'inventory_item_id' => $inventoryItem->id,
                            'type'              => 'inbound',
                            'quantity'          => $amount,
                            'reference'         => $order->po_number,
                            'remarks'           => 'Restored from cancelled order',
                            'recorded_by'       => auth()->id(),
                            'movement_date'     => now(),
                        ]);
                    }
                }
            }

            // ✅ UPDATE STATUS
            $order->status       = $request->status;
            $order->confirmed_at = $request->status === 'Confirmed' ? now() : null;
            $order->save();

            ActivityLog::record('Orders', 'Updated Order Status', "Order {$order->po_number} marked as {$request->status}.");

            return back()->with('success', "Order {$order->po_number} has been {$request->status}.");
        }

            public function store(Request $request)
            {
                $request->validate([
                    'client_id'            => 'nullable|integer',
                    'client_name'          => 'required|string',
                    'order_date'           => 'required|date',
                    'notes'                => 'nullable|string',
                    'items'                => 'required|array|min:1',
                    'items.*.product'      => 'required|string',
                    'items.*.variant_id'   => 'required|integer',
                    'items.*.quantity_pcs' => 'required|numeric|min:1',
                ]);

                $poNumber = 'PO-' . now()->format('Y') . '-' . strtoupper(substr(uniqid(), -5));

                $order = Order::create([
                    'po_number'   => $poNumber,
                    'client_id'   => $request->client_id ?? null,
                    'client_name' => $request->client_name,
                    'order_date'  => $request->order_date,
                    'quantity'    => 0,
                    'unit'        => 'kg',
                    'status'      => 'Pending',
                    'notes'       => $request->notes ?? null,
                    'created_by'  => auth()->id(),
                ]);

                $totalKgAll = 0;
                foreach ($request->items as $item) {
                    $variant     = ProductVariant::findOrFail($item['variant_id']);
                    $totalKg     = ($variant->weight_grams / 1000) * $item['quantity_pcs'];
                    $totalKgAll += $totalKg;

                    OrderItem::create([
                        'order_id'        => $order->id,
                        'cheese_product'  => $item['product'],
                        'variant_name'    => $variant->variant_name,
                        'weight_grams'    => $variant->weight_grams,
                        'quantity_pieces' => $item['quantity_pcs'],
                        'total_kg'        => $totalKg,
                    ]);
                }

                $order->update(['quantity' => $totalKgAll]);

                ActivityLog::record('Orders', 'Created Order', "Order {$poNumber} created for {$request->client_name}.");

                return back()->with('success', "Order {$poNumber} created successfully!");
            }

            public function archive(Order $order)
        {
            // Prevent double archive
            if ($order->is_archived) {
                return back()->withErrors([
                    'error' => 'Order is already archived.'
                ]);
            }

            // Business rule: only finished orders can be archived
            if (!in_array($order->status, ['Completed', 'Cancelled'])) {
                return back()->withErrors([
                    'error' => 'Only completed or cancelled orders can be archived.'
                ]);
            }

            $order->update(['is_archived' => true]);

            ActivityLog::record('Orders', 'Archived Order', "Order {$order->po_number} archived.");

            return back()->with('success', "Order {$order->po_number} has been archived.");
        }
        }