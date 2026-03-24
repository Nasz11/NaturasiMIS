<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\InventoryItem;
use App\Models\ProductionBatch;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $type      = $request->get('report_type', 'inventory');

        // Sanitize empty date strings to null
        $startDate = $request->get('start_date') ?: null;
        $endDate   = $request->get('end_date') ?: null;

        $data = match ($type) {
            'production' => $this->productionReport($startDate, $endDate),
            'batches'    => $this->batchesReport($startDate, $endDate),
            'activity'   => $this->activityReport($startDate, $endDate),
            default      => $this->inventoryReport($startDate, $endDate),
        };

        // Chart data (monthly production & inventory for the last 6 months)
        $chartLabels     = [];
        $chartProduction = [];
        $chartInventory  = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $chartLabels[]     = $month->format('M Y');
            $chartProduction[] = (float) ProductionBatch::whereYear('production_date', $month->year)
                ->whereMonth('production_date', $month->month)
                ->sum('quantity');
            $chartInventory[]  = (float) InventoryItem::whereYear('updated_at', $month->year)
                ->whereMonth('updated_at', $month->month)
                ->sum('quantity');
        }

        ActivityLog::record('Reports', 'Generated Report', ucfirst($type) . ' report generated.');

        $reportData = $data;
        return view('reports.index', compact(
            'reportData', 'type', 'startDate', 'endDate',
            'chartLabels', 'chartProduction', 'chartInventory'
        ));
    }

    // inventory_items: product_name, category, quantity, unit, status, updated_at
    private function inventoryReport($start = null, $end = null)
    {
        return InventoryItem::when($start, fn($q) => $q->whereDate('updated_at', '>=', $start))
            ->when($end,   fn($q) => $q->whereDate('updated_at', '<=', $end))
            ->orderBy('product_name')
            ->get()
            ->map(fn($i) => [
                'date'        => $i->updated_at->format('Y-m-d'),
                'module'      => 'Inventory',
                'description' => $i->product_name . ' — ' . $i->category,
                'value'       => $i->quantity . ' ' . $i->unit,
                'status'      => $i->status,
            ]);
    }

    // production_batches: batch_number, product_type, quantity, production_date, status
    private function productionReport($start, $end)
    {
        return ProductionBatch::with('staff')
            ->when($start, fn($q) => $q->whereDate('production_date', '>=', $start))
            ->when($end,   fn($q) => $q->whereDate('production_date', '<=', $end))
            ->orderByDesc('production_date')
            ->get()
            ->map(fn($b) => [
                'date'        => $b->production_date->format('Y-m-d'),
                'module'      => 'Production',
                'description' => 'Batch ' . $b->batch_number . ' — ' . $b->product_type
                                 . ($b->staff ? ' (by ' . $b->staff->username . ')' : ''),
                'value'       => $b->quantity . ' kg',
                'status'      => $b->status,
            ]);
    }

    // batches: batch_id, cheese_type, quantity, start_date, completion_date, status
    private function batchesReport($start, $end)
    {
        return Batch::with('staff')
            ->when($start, fn($q) => $q->whereDate('start_date', '>=', $start))
            ->when($end,   fn($q) => $q->whereDate('start_date', '<=', $end))
            ->orderByDesc('start_date')
            ->get()
            ->map(fn($b) => [
                'date'        => $b->start_date->format('Y-m-d'),
                'module'      => 'Batches',
                'description' => 'Batch ' . $b->batch_id . ' — ' . $b->cheese_type
                                 . ($b->staff ? ' (by ' . $b->staff->username . ')' : ''),
                'value'       => $b->quantity . ' kg',
                'status'      => $b->status,
            ]);
    }

    // activity_logs: username, module, action, details, created_at
    private function activityReport($start, $end)
    {
        return ActivityLog::with('user')
            ->when($start, fn($q) => $q->whereDate('created_at', '>=', $start))
            ->when($end,   fn($q) => $q->whereDate('created_at', '<=', $end))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($l) => [
                'date'        => $l->created_at->format('Y-m-d'),
                'module'      => $l->module,
                'description' => $l->username . ': ' . $l->action,
                'value'       => $l->details ?? '—',
                'status'      => 'Logged',
            ]);
    }
}