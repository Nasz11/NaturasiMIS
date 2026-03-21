<?php
// ─────────────────────────────────────────────
// BatchController.php
// ─────────────────────────────────────────────
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\User;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function index()
    {
        $batches = Batch::with('staff')->orderByDesc('start_date')->get();
       $staffList = User::where('status', 'Active')->get();
return view('batches.index', compact('batches', 'staffList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'batch_id'        => 'required|string|unique:batches,batch_id',
            'cheese_type'     => 'required|string',
            'quantity'        => 'required|numeric|min:0.01',
            'start_date'      => 'required|date',
            'completion_date' => 'required|date|after_or_equal:start_date',
            'status'          => 'required|string',
            'staff_id'        => 'required|exists:users,id',
        ]);

        $batch = Batch::create($request->only(
            'batch_id', 'cheese_type', 'quantity',
            'start_date', 'completion_date', 'status', 'staff_id', 'remarks'
        ));

        ActivityLog::record('Batches', 'Added Batch', "Batch {$batch->batch_id} ({$batch->cheese_type}) added.");

        return back()->with('success', 'Batch record added successfully.');
    }

    public function update(Request $request, Batch $batch)
    {
        $request->validate([
            'status'  => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $batch->update($request->only('status', 'remarks'));

        ActivityLog::record('Batches', 'Updated Batch', "Batch {$batch->batch_id} updated to {$batch->status}.");

        return back()->with('success', 'Batch updated successfully.');
    }

    public function destroy(Batch $batch)
    {
        $id = $batch->batch_id;
        $batch->delete();

        ActivityLog::record('Batches', 'Deleted Batch', "Batch {$id} deleted.");

        return back()->with('success', 'Batch deleted.');
    }
}
