<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\InventoryItem;
use App\Models\ProductionBatch;

class DashboardController extends Controller
{
    public function index()
    {
        $totalStock    = Batch::whereIn('status', ['In Production', 'Curing', 'Ready for Packaging'])->count();
        $todayOutput   = ProductionBatch::whereDate('production_date', today())->sum('quantity');
        $lowStockItems = InventoryItem::where('status', 'Low Stock')->orWhere('status', 'Out of Stock')->get();
        $lowStockCount = $lowStockItems->count();

        $latestBatches = Batch::with('staff')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

         $todayBatches = ProductionBatch::with('staff')
            ->whereDate('production_date', today())
            ->get();

        $recentNotifications = ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Weekly production data for chart (last 7 days)
        $productionChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $productionChartData[] = [
                'label' => $date->format('D'),
                'value' => ProductionBatch::whereDate('production_date', $date)->sum('quantity'),
            ];
        }

       return view('dashboard.index', compact(
            'totalStock', 'todayOutput', 'lowStockCount',
            'lowStockItems', 'latestBatches', 'recentNotifications',
            'productionChartData', 'todayBatches'
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

    $batches    = Batch::where('batch_id', 'like', "%{$q}%")
                    ->orWhere('cheese_type', 'like', "%{$q}%")->get();

    ActivityLog::record('Search', 'Searched', "Searched for: {$q}");

    return view('search.index', compact('q', 'inventory', 'production', 'batches'));
}

}
    