<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Recipe;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
{
    $search = $request->get('search');

    $orders = Order::with('createdBy')
        ->when($search, fn($q) => $q->where('po_number', 'like', "%{$search}%")
            ->orWhere('cheese_product', 'like', "%{$search}%")
            ->orWhere('status', 'like', "%{$search}%"))
        ->latest()
        ->paginate(10)
        ->withQueryString();

    $cheeseProducts = [
        'Burrata', 'Stracciatella', 'Cherry Mozzarella',
        'Traditional Mozzarella', 'Provola', 'Mozzarella di Latte',
    ];

    return view('orders.index', compact('orders', 'cheeseProducts', 'search'));
}

    public function preview(Request $request)
    {
        $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.product'  => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $preview = [];

        foreach ($request->items as $item) {
            $product  = $item['product'];
            $quantity = $item['quantity'];

            $recipes = Recipe::where('cheese_product', $product)->get();

            foreach ($recipes as $recipe) {
                $needed = $recipe->quantity_needed * $quantity;
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

        // Check if any ingredient is insufficient
        $insufficient = collect($preview)->filter(fn($i) => $i['needed'] > $i['available'])->count();

        return response()->json([
            'preview'      => array_values($preview),
            'insufficient' => $insufficient,
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.product'  => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        // Compute all ingredient deductions first
        $deductions = [];

        foreach ($request->items as $item) {
            $product  = $item['product'];
            $quantity = $item['quantity'];

            $recipes = Recipe::where('cheese_product', $product)->get();

            foreach ($recipes as $recipe) {
                $needed = $recipe->quantity_needed * $quantity;
                $key    = $recipe->ingredient_name;

                $deductions[$key] = ($deductions[$key] ?? 0) + $needed;
            }
        }

        // Check availability before deducting
        foreach ($deductions as $ingredientName => $needed) {
            $inventoryItem = InventoryItem::where('product_name', $ingredientName)->first();
            if (!$inventoryItem || $inventoryItem->quantity < $needed) {
                return back()->withErrors([
                    'insufficient' => "Not enough stock for: {$ingredientName}. Available: " .
                        ($inventoryItem ? $inventoryItem->quantity : 0) . ", Needed: {$needed}",
                ]);
            }
        }

        // All good — deduct from inventory and save orders
        foreach ($deductions as $ingredientName => $needed) {
            $inventoryItem           = InventoryItem::where('product_name', $ingredientName)->first();
            $inventoryItem->quantity -= $needed;
            $inventoryItem->save();
        }

        foreach ($request->items as $item) {
    $poNumber = 'PO-' . now()->format('Ymd') . '-' . strtoupper(uniqid());
    Order::create([
        'po_number'      => $poNumber,
        'cheese_product' => $item['product'],
        'quantity'       => $item['quantity'],
        'unit'           => 'kg',
        'status'         => 'Confirmed',
        'notes'          => $request->notes ?? null,
        'created_by'     => auth()->id(),
        'confirmed_at'   => now(),
    ]);
}

        ActivityLog::record('Orders', 'Confirmed Order', 'Production order confirmed and inventory deducted.');

        return back()->with('success', 'Order confirmed! Inventory has been updated.');
    }
    public function updateStatus(Request $request, Order $order)
{
    $request->validate(['status' => 'required|in:Confirmed,Cancelled']);

    $order->status = $request->status;
    $order->confirmed_at = $request->status === 'Confirmed' ? now() : null;
    $order->save();

    ActivityLog::record('Orders', 'Updated Order Status', "Order {$order->po_number} marked as {$request->status}.");

    return back()->with('success', "Order {$order->po_number} has been {$request->status}.");
}
}