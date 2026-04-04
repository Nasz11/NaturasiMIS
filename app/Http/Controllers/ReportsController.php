<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ActivityLog;
use App\Models\InventoryItem;
use App\Models\ProductionBatch;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $type      = $request->get('report_type', 'inventory');
        $startDate = $request->get('start_date') ?: null;
        $endDate   = $request->get('end_date') ?: null;

        $data = match ($type) {
            'production' => $this->productionReport($startDate, $endDate),
            'activity'   => $this->activityReport($startDate, $endDate),
            'orders'     => $this->ordersReport($startDate, $endDate),
            default      => $this->inventoryReport($startDate, $endDate),
        };

        $chartLabels   = [];
        $chartThisYear = [];
        $chartLastYear = [];

        if ($type === 'production') {
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $chartLabels[]   = $month->format('M');
                $chartThisYear[] = (float) ProductionBatch::whereYear('production_date', $month->year)->whereMonth('production_date', $month->month)->sum('quantity');
                $chartLastYear[] = (float) ProductionBatch::whereYear('production_date', $month->year - 1)->whereMonth('production_date', $month->month)->sum('quantity');
            }
        } elseif ($type === 'orders') {
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $chartLabels[]   = $month->format('M');
                $chartThisYear[] = (float) Order::whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum('quantity');
                $chartLastYear[] = (float) Order::whereYear('created_at', $month->year - 1)->whereMonth('created_at', $month->month)->sum('quantity');
            }
       } elseif ($type === 'inventory') {
            $items = InventoryItem::where('is_archived', false)->where('category', 'Raw Materials')->get();
            foreach ($items as $item) {
                $chartLabels[]   = $item->product_name;
                $chartThisYear[] = (float) $item->quantity;
                $chartLastYear[] = null;
            }
        }

       $bestSelling = \App\Models\OrderItem::selectRaw('cheese_product, SUM(total_kg) as total')
            ->groupBy('cheese_product')
            ->orderByDesc('total')
            ->first();

     $slowMoving = \App\Models\OrderItem::selectRaw('cheese_product, SUM(total_kg) as total')
            ->groupBy('cheese_product')
            ->orderBy('total')
            ->first();

        if ($request->has('report_type')) {
            ActivityLog::record('Reports', 'Generated Report', ucfirst($type) . ' report generated.');
        }

        $reportData = $data;
        return view('reports.index', compact(
            'reportData', 'type', 'startDate', 'endDate',
            'chartLabels', 'chartThisYear', 'chartLastYear',
            'bestSelling', 'slowMoving'
        ));
    }

   private function inventoryReport($startDate, $endDate)
{
    return InventoryItem::where('is_archived', false)
        ->where('category', 'Raw Materials')
        ->get();
}

private function productionReport($startDate, $endDate)
{
    $query = ProductionBatch::query();
    if ($startDate) $query->whereDate('production_date', '>=', $startDate);
    if ($endDate)   $query->whereDate('production_date', '<=', $endDate);
    return $query->latest('production_date')->get();
}

private function ordersReport($startDate, $endDate)
{
    $query = Order::with('createdBy');
    if ($startDate) $query->whereDate('created_at', '>=', $startDate);
    if ($endDate)   $query->whereDate('created_at', '<=', $endDate);
    return $query->latest()->get();
}

private function activityReport($startDate, $endDate)
{
    $query = ActivityLog::with('user');
    if ($startDate) $query->whereDate('created_at', '>=', $startDate);
    if ($endDate)   $query->whereDate('created_at', '<=', $endDate);
    return $query->latest()->get();
}
}