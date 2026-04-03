<?php
namespace App\Http\Controllers;
use App\Models\Batch;
use Illuminate\Http\Request;
class BatchController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $batches = Batch::with('staff')
            ->when($search, fn($q) => $q->where('batch_id', 'like', "%{$search}%")
                ->orWhere('cheese_type', 'like', "%{$search}%"))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('start_date')
            ->paginate(10)
            ->withQueryString();

        return view('batches.index', compact('batches', 'search', 'status'));
    }
}