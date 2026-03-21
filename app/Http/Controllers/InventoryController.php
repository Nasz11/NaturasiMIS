<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $items = InventoryItem::with('updatedBy')->orderByDesc('updated_at')->get();
        return view('inventory.index', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name'  => 'required|string|max:255',
            'category'      => 'required|string|max:255',
            'quantity'      => 'required|numeric|min:0',
            'unit'          => 'required|string',
            'reorder_level' => 'required|numeric|min:0',
        ]);

        $item = InventoryItem::create([
            'product_name'  => $request->product_name,
            'category'      => $request->category,
            'quantity'      => $request->quantity,
            'unit'          => $request->unit,
            'reorder_level' => $request->reorder_level,
            'updated_by'    => auth()->id(),
        ]);

        ActivityLog::record('Inventory', 'Added Item', "Added {$item->product_name} ({$item->quantity} {$item->unit})");

        return back()->with('success', 'Inventory item added successfully.');
    }

    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $request->validate([
            'product_name'  => 'required|string|max:255',
            'category'      => 'required|string|max:255',
            'quantity'      => 'required|numeric|min:0',
            'unit'          => 'required|string',
            'reorder_level' => 'required|numeric|min:0',
        ]);

        $inventoryItem->update([
            'product_name'  => $request->product_name,
            'category'      => $request->category,
            'quantity'      => $request->quantity,
            'unit'          => $request->unit,
            'reorder_level' => $request->reorder_level,
            'updated_by'    => auth()->id(),
        ]);

        ActivityLog::record('Inventory', 'Updated Item', "Updated {$inventoryItem->product_name}");

        return back()->with('success', 'Inventory item updated successfully.');
    }

    public function destroy(InventoryItem $inventoryItem)
    {
        $name = $inventoryItem->product_name;
        $inventoryItem->delete();

        ActivityLog::record('Inventory', 'Deleted Item', "Deleted {$name}");

        return back()->with('success', 'Item removed from inventory.');
    }
}
