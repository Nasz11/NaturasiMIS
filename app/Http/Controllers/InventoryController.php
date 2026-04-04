<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
{
    $date = $request->get('date', now()->toDateString());
    $selectedDate = \Carbon\Carbon::parse($date);

   $items = InventoryItem::with(['updatedBy', 'movements'])
        ->where('is_archived', false)
        ->where('category', 'Raw Materials')
        ->get()
        ->each(function ($item) {
            $computed = $item->computedQuantity();
            if ($item->quantity != $computed) {
                $item->quantity = $computed;
                $item->saveQuietly();
            }
        })
        ->sortBy('quantity')
        ->values();

    $archivedItems = InventoryItem::with('updatedBy')
        ->where('is_archived', true)
        ->orderBy('category')
        ->orderBy('product_name')
        ->get();

    $inboundMovements = InventoryMovement::with(['item', 'recordedBy'])
        ->where('type', 'inbound')
        ->whereDate('movement_date', $selectedDate)
        ->orderByDesc('movement_date')
        ->get()
        ->groupBy('inventory_item_id');

    $outboundMovements = InventoryMovement::with(['item', 'recordedBy'])
        ->where('type', 'outbound')
        ->whereDate('movement_date', $selectedDate)
        ->orderByDesc('movement_date')
        ->get()
        ->groupBy('inventory_item_id');

    return view('inventory.index', compact(
        'items', 'archivedItems',
        'date',
        'inboundMovements', 'outboundMovements'
    ));
}

    public function store(Request $request)
    {
        $request->validate([
            'product_name'  => 'required|string|max:255',
            'category'      => 'required|string|max:255',
            'unit'          => 'required|string',
            'reorder_level' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
        ]);

        $item = InventoryItem::create([
            'product_name'  => $request->product_name,
            'category'      => $request->category,
            'quantity'      => 0, // always starts at 0, movements will update it
            'unit'          => $request->unit,
            'reorder_level' => $request->reorder_level,
            'cost_per_unit' => $request->cost_per_unit,
            'updated_by'    => auth()->id(),
        ]);

        ActivityLog::record('Inventory', 'Added Item', "Added {$item->product_name}");
        return back()->with('success', 'Inventory item added successfully.');
    }

    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $request->validate([
            'product_name'  => 'required|string|max:255',
            'category'      => 'required|string|max:255',
            'unit'          => 'required|string',
            'reorder_level' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
        ]);

        $inventoryItem->update([
            'product_name'  => $request->product_name,
            'category'      => $request->category,
            'unit'          => $request->unit,
            'reorder_level' => $request->reorder_level,
            'cost_per_unit' => $request->cost_per_unit,
            'updated_by'    => auth()->id(),
        ]);

        ActivityLog::record('Inventory', 'Updated Item', "Updated {$inventoryItem->product_name}");
        return back()->with('success', 'Inventory item updated successfully.');
    }

    public function storeMovement(Request $request)
    {
        $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'type'              => 'required|in:inbound,outbound',
            'quantity'          => 'required|numeric|min:0.01',
            'reference'         => 'nullable|string|max:255',
            'remarks'           => 'nullable|string|max:255',
            'movement_date'     => 'required|date',
        ]);

        $item = InventoryItem::findOrFail($request->inventory_item_id);

        // Check sufficient stock for outbound
        if ($request->type === 'outbound') {
            $currentStock = $item->computedQuantity();
            if ($currentStock < $request->quantity) {
                return back()->withErrors(['quantity' => "Insufficient stock. Current stock: {$currentStock} {$item->unit}"]);
            }
        }

        InventoryMovement::create([
            'inventory_item_id' => $request->inventory_item_id,
            'type'              => $request->type,
            'quantity'          => $request->quantity,
            'reference'         => $request->reference,
            'remarks'           => $request->remarks,
            'recorded_by'       => auth()->id(),
            'movement_date'     => $request->movement_date,
        ]);

        // Sync quantity
        $newQty = $item->computedQuantity();
        $item->quantity = $newQty;
        $item->saveQuietly();

        $type = ucfirst($request->type);
        ActivityLog::record('Inventory', "{$type} Movement", "{$type} {$request->quantity} {$item->unit} of {$item->product_name}");
        return back()->with('success', "Movement recorded successfully.");
    }

    public function archive(InventoryItem $inventoryItem)
    {
        $inventoryItem->update(['is_archived' => true]);
        ActivityLog::record('Inventory', 'Archived Item', "Archived {$inventoryItem->product_name}");
        return back()->with('success', "{$inventoryItem->product_name} has been archived.");
    }

    public function restore(InventoryItem $inventoryItem)
    {
        $inventoryItem->update(['is_archived' => false]);
        ActivityLog::record('Inventory', 'Restored Item', "Restored {$inventoryItem->product_name}");
        return back()->with('success', "{$inventoryItem->product_name} has been restored.");
    }

    public function destroy(InventoryItem $inventoryItem)
    {
        $name = $inventoryItem->product_name;
        $inventoryItem->delete();
        ActivityLog::record('Inventory', 'Deleted Item', "Permanently deleted {$name}");
        return back()->with('success', 'Item permanently deleted.');
    }
}