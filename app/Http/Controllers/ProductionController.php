<?php
namespace App\Http\Controllers;
use App\Models\ActivityLog;
use App\Models\ProductionBatch;
use App\Models\User;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
   public function index(Request $request)
{
    $search = $request->get('search');

    $batches = ProductionBatch::with('staff')
        ->where('is_archived', false)
        ->when($search, fn($q) => $q->where('batch_number', 'like', "%{$search}%")
            ->orWhere('product_type', 'like', "%{$search}%")
            ->orWhere('status', 'like', "%{$search}%"))
        ->orderByDesc('production_date')
        ->paginate(10)
        ->withQueryString();

    $archivedBatches = ProductionBatch::with('staff')
        ->where('is_archived', true)
        ->orderByDesc('production_date')
        ->paginate(10)
        ->withQueryString();

$staff = User::where('status', 'Active')->get();

    $todayOrders = \App\Models\OrderItem::whereHas('order', function($q) {
            $q->where('status', 'Confirmed')
              ->whereDate('order_date', today());
        })
        ->get()
        ->groupBy('cheese_product')
        ->map(function($items) {
            return [
                'total_pcs' => $items->sum('quantity_pieces'),
                'total_kg'  => $items->sum('total_kg'),
                'clients'   => $items->pluck('order.client_name')->unique()->count(),
            ];
        });

   $todayBatchProducts = ProductionBatch::whereDate('production_date', today())
        ->where('is_archived', false)
        ->pluck('product_type')
        ->toArray();

    return view('production.index', compact('batches', 'archivedBatches', 'staff', 'search', 'todayOrders', 'todayBatchProducts'));
}

    public function store(Request $request)
    {
        $request->validate([
            'batch_number'    => 'required|string|unique:production_batches,batch_number',
            'product_type'    => 'required|string',
            'quantity'        => 'required|numeric|min:0.01',
            'production_date' => 'required|date',
            'status'          => 'required|in:In Production,Curing,Completed',
            'staff_id'        => 'nullable|exists:users,id',
        ]);
        $batch = ProductionBatch::create($request->only(
            'batch_number', 'product_type', 'quantity',
            'production_date', 'status', 'remarks', 'staff_id'
        ));
        ActivityLog::record('Production', 'Created Batch', "Batch {$batch->batch_number} ({$batch->product_type}) added.");
        return back()->with('success', 'Production batch added successfully.');
    }

    public function update(Request $request, ProductionBatch $productionBatch)
    {
        $request->validate([
            'batch_number'    => 'required|string|unique:production_batches,batch_number,' . $productionBatch->id,
            'product_type'    => 'required|string',
            'quantity'        => 'required|numeric|min:0.01',
            'production_date' => 'required|date',
            'status'          => 'required|in:In Production,Curing,Completed',
        ]);
       // Enforce workflow order
        $workflow = ['In Production', 'Curing', 'Completed'];
        $currentIndex = array_search($productionBatch->status, $workflow);
        $newIndex = array_search($request->status, $workflow);

        if ($newIndex < $currentIndex) {
            return back()->withErrors(['status' => 'Cannot move batch backwards in the workflow.']);
        }

       $productionBatch->update($request->only(
            'batch_number', 'product_type', 'quantity',
            'production_date', 'status', 'remarks', 'staff_id'
        ));

        // Auto-deliver orders when batch is completed
        if ($request->status === 'Completed') {
            \App\Models\Order::whereHas('items', function($q) use ($productionBatch) {
                $q->where('cheese_product', $productionBatch->product_type);
            })
            ->where('status', 'Confirmed')
            ->whereDate('order_date', $productionBatch->production_date)
           ->update(['status' => 'Completed']);
        }

        ActivityLog::record('Production', 'Updated Batch', "Batch {$productionBatch->batch_number} updated.");
        return back()->with('success', 'Production batch updated.');
    }

    public function archive(ProductionBatch $productionBatch)
    {
        $productionBatch->update(['is_archived' => true]);
        ActivityLog::record('Production', 'Archived Batch', "Batch {$productionBatch->batch_number} archived.");
        return back()->with('success', "Batch {$productionBatch->batch_number} has been archived.");
    }

    public function restore(ProductionBatch $productionBatch)
    {
        $productionBatch->update(['is_archived' => false]);
        ActivityLog::record('Production', 'Restored Batch', "Batch {$productionBatch->batch_number} restored.");
        return back()->with('success', "Batch {$productionBatch->batch_number} has been restored.");
    }

    public function destroy(ProductionBatch $productionBatch)
    {
        $num = $productionBatch->batch_number;
        $productionBatch->delete();
        ActivityLog::record('Production', 'Deleted Batch', "Batch {$num} permanently deleted.");
        return back()->with('success', 'Production batch permanently deleted.');
    }
    public function nextBatchNumber()
    {
       $last = ProductionBatch::orderByDesc('id')
            ->value('batch_number');

        if ($last && preg_match('/B-(\d{4})-(\d+)/', $last, $m)) {
            $next = str_pad((int)$m[2] + 1, 3, '0', STR_PAD_LEFT);
            $number = 'B-' . $m[1] . '-' . $next;
        } else {
            $number = 'B-' . now()->year . '-001';
        }

        return response()->json(['batch_number' => $number]);
    }
}