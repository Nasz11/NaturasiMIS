<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InventoryItem;
use App\Models\ProductionBatch;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $totalStock    = ProductionBatch::whereIn('status', ['In Production', 'Curing', 'Ready for Packaging'])->count();
        $todayOutput   = ProductionBatch::whereDate('production_date', today())->sum('quantity');
        $lowStockItems = InventoryItem::where('status', 'Low Stock')->orWhere('status', 'Out of Stock')->get();
        $lowStockCount = $lowStockItems->count();

        $todayBatches = ProductionBatch::with('staff')
            ->whereDate('production_date', today())
            ->get();

        $recentNotifications = ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $productionChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $productionChartData[] = [
                'label' => $date->format('D'),
                'value' => ProductionBatch::whereDate('production_date', $date)->sum('quantity'),
            ];
        }

        $todayOrders = \App\Models\Order::with('items')
            ->whereDate('confirmed_at', today())
            ->whereIn('status', ['Confirmed', 'Completed'])
            ->get();

        $pendingOrdersCount = \App\Models\Order::where('status', 'Pending')->count();

$expiringItems = \App\Models\InventoryMovement::with('item')
    ->where('type', 'inbound')
    ->whereNotNull('expiry_date')
    ->whereDate('expiry_date', '>=', today())
    ->whereDate('expiry_date', '<=', now()->addDays(7))
    ->get()
    ->groupBy('inventory_item_id');

$expiredItems = \App\Models\InventoryMovement::with('item')
    ->where('type', 'inbound')
    ->whereNotNull('expiry_date')
    ->whereDate('expiry_date', '<', today())
    ->get()
    ->groupBy('inventory_item_id');

return view('dashboard.index', compact(
    'totalStock', 'todayOutput', 'lowStockCount',
    'lowStockItems', 'recentNotifications',
    'productionChartData', 'todayBatches',
    'todayOrders', 'pendingOrdersCount',
    'expiringItems', 'expiredItems'
));
    }

    public function search(\Illuminate\Http\Request $request)
    {
        $q = $request->get('q');
        if (!$q) return redirect()->route('dashboard');

        $inventory  = InventoryItem::where('product_name', 'like', "%{$q}%")
                        ->orWhere('category', 'like', "%{$q}%")->get();

        $production = ProductionBatch::where('batch_number', 'like', "%{$q}%")
                        ->orWhere('product_type', 'like', "%{$q}%")->get();

        ActivityLog::record('Search', 'Searched', "Searched for: {$q}");

        return view('search.index', compact('q', 'inventory', 'production'));
    }
}