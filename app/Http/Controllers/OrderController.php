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
        public function index(Request $request)
        {
            $search     = $request->get('search');
            $activeTab  = $request->get('tab', 'orders');

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

            $preview = [];

            foreach ($request->items as $item) {
            $totalKg = isset($item['total_kg']) ? $item['total_kg'] : (($item['variant_id'] / 1000) * $item['quantity_pcs']);
                $recipes = Recipe::where('cheese_product', $item['product'])->get();

                foreach ($recipes as $recipe) {
                    $needed = $recipe->quantity_needed * $totalKg;
                    $key    = $recipe->ingredient_name;

                    if (isset($preview[$key])) {
                        $preview[$key]['needed'] += $needed;
                    } else {
                        $inventoryItem = InventoryItem::where('product_name', $recipe->ingredient_name)->first();
                        $preview[$key] = [
                            'ingredient' => $recipe->ingredient_name,
                            'needed'     => $needed,
                            'available'  => $inventoryItem ? $inventoryItem->quantity : 0,
                            'unit'       => $recipe->unit,
                        ];
                    }
                }
            }

            $insufficient = collect($preview)->filter(fn($i) => $i['needed'] > $i['available'])->count();

            return response()->json([
                'preview'      => array_values($preview),
                'insufficient' => $insufficient,
            ]);
        }

        public function confirm(Request $request)
        {
            $request->validate([
                'client_id'   => 'nullable|integer',
                'client_name' => 'required|string',
                'order_date'  => 'required|date',
                'notes'       => 'nullable|string',
                'items'                => 'required|array|min:1',
                'items.*.product'      => 'required|string',
                'items.*.variant_id'   => 'required|integer',
                'items.*.quantity_pcs' => 'required|numeric|min:1',
            ]);

            $deductions = [];

            foreach ($request->items as $item) {
                $variant = ProductVariant::findOrFail($item['variant_id']);
                $totalKg = ($variant->weight_grams / 1000) * $item['quantity_pcs'];
                $recipes = Recipe::where('cheese_product', $item['product'])->get();

                foreach ($recipes as $recipe) {
                    $needed = $recipe->quantity_needed * $totalKg;
                    $key    = $recipe->ingredient_name;
                    $deductions[$key] = ($deductions[$key] ?? 0) + $needed;
                }
            }

            // Check availability
            foreach ($deductions as $ingredientName => $needed) {
                $inventoryItem = InventoryItem::where('product_name', $ingredientName)->first();
                if (!$inventoryItem || $inventoryItem->quantity < $needed) {
                    return back()->withErrors([
                        'insufficient' => "Not enough stock for: {$ingredientName}. Available: " .
                            ($inventoryItem ? $inventoryItem->quantity : 0) . ", Needed: {$needed}",
                    ])->with('tab', 'orders');
                }
            }

            // Deduct inventory
            foreach ($deductions as $ingredientName => $needed) {
                $inventoryItem           = InventoryItem::where('product_name', $ingredientName)->first();
                $inventoryItem->quantity -= $needed;
                $inventoryItem->save();
            }

            // Create order
            $poNumber = 'PO-' . now()->format('Y') . '-' . strtoupper(substr(uniqid(), -5));

            $order = Order::create([
                'po_number'   => $poNumber,
                'client_id'   => $request->client_id ?? null,
                'client_name' => $request->client_name,
                'order_date'  => $request->order_date,
                'quantity'    => 0,
                'unit'        => 'kg',
                'status'      => 'Confirmed',
                'notes'       => $request->notes ?? null,
                'created_by'  => auth()->id(),
                'confirmed_at'=> now(),
            ]);

            // Save order items
            $totalKgAll = 0;
            foreach ($request->items as $item) {
                $variant = ProductVariant::findOrFail($item['variant_id']);
                $totalKg = ($variant->weight_grams / 1000) * $item['quantity_pcs'];
                $totalKgAll += $totalKg;

                OrderItem::create([
                    'order_id'       => $order->id,
                    'cheese_product' => $item['product'],
                    'variant_name'   => $variant->variant_name,
                    'weight_grams'   => $variant->weight_grams,
                    'quantity_pieces'=> $item['quantity_pcs'],
                    'total_kg'       => $totalKg,
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
                'client_id'   => 'nullable|integer',
                'client_name' => 'required|string',
                'order_date'  => 'required|date',
                'notes'       => 'nullable|string',
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

            // Delete old items and re-insert
            $order->items()->delete();

            $totalKgAll = 0;
            foreach ($request->items as $item) {
                $variant = ProductVariant::findOrFail($item['variant_id']);
                $totalKg = ($variant->weight_grams / 1000) * $item['quantity_pcs'];
                $totalKgAll += $totalKg;

                OrderItem::create([
                    'order_id'       => $order->id,
                    'cheese_product' => $item['product'],
                    'variant_name'   => $variant->variant_name,
                    'weight_grams'   => $variant->weight_grams,
                    'quantity_pieces'=> $item['quantity_pcs'],
                    'total_kg'       => $totalKg,
                ]);
            }

            $order->update(['quantity' => $totalKgAll]);

            ActivityLog::record('Orders', 'Updated Order', "Order {$order->po_number} updated.");

            return back()->with('success', "Order {$order->po_number} updated successfully!");
        }

        public function updateStatus(Request $request, Order $order)
    {
       $request->validate(['status' => 'required|in:Confirmed,Cancelled,Completed']);

        if ($request->status === 'Confirmed') {
            $deductions = [];

            foreach ($order->items as $item) {
                $recipes = Recipe::where('cheese_product', $item->cheese_product)->get();
                foreach ($recipes as $recipe) {
                    $needed = $recipe->quantity_needed * $item->total_kg;
                    $deductions[$recipe->ingredient_name] = ($deductions[$recipe->ingredient_name] ?? 0) + $needed;
                }
            }

            // Check availability
            foreach ($deductions as $ingredientName => $needed) {
                $inventoryItem = InventoryItem::where('product_name', $ingredientName)->first();
                if (!$inventoryItem || $inventoryItem->quantity < $needed) {
                    return back()->withErrors([
                        'insufficient' => "Not enough stock for: {$ingredientName}. Available: " .
                            ($inventoryItem ? $inventoryItem->quantity : 0) . ", Needed: {$needed}",
                    ])->with('tab', 'orders');
                }
            }

        // Deduct inventory
            foreach ($deductions as $ingredientName => $needed) {
                $inventoryItem = InventoryItem::where('product_name', $ingredientName)->first();
                $inventoryItem->quantity -= $needed;
                $inventoryItem->save();

                // Record outbound movement
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

        $order->status       = $request->status;
        $order->confirmed_at = $request->status === 'Confirmed' ? now() : null;
        $order->save();

        ActivityLog::record('Orders', 'Updated Order Status', "Order {$order->po_number} marked as {$request->status}.");

        return back()->with('success', "Order {$order->po_number} has been {$request->status}.");
    }
        

        public function store(Request $request)
        {
            $request->validate([
                'client_id'   => 'nullable|integer',
                'client_name' => 'required|string',
                'order_date'  => 'required|date',
                'notes'       => 'nullable|string',
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
                $variant = ProductVariant::findOrFail($item['variant_id']);
                $totalKg = ($variant->weight_grams / 1000) * $item['quantity_pcs'];
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
            if ($order->status !== 'Cancelled') {
                return back()->withErrors(['error' => 'Only cancelled orders can be archived.']);
            }
            $order->update(['is_archived' => true]);
            ActivityLog::record('Orders', 'Archived Order', "Order {$order->po_number} archived.");
            return back()->with('success', "Order {$order->po_number} has been archived.");
        }
    }