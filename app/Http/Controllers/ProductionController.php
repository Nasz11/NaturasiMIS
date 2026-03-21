<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ProductionBatch;
use App\Models\User;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    public function index()
    {
        $batches = ProductionBatch::with('staff')->orderByDesc('production_date')->get();
        $staff   = User::where('status', 'Active')->get();
        return view('production.index', compact('batches', 'staff'));
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

        $productionBatch->update($request->only(
            'batch_number', 'product_type', 'quantity',
            'production_date', 'status', 'remarks', 'staff_id'
        ));

        ActivityLog::record('Production', 'Updated Batch', "Batch {$productionBatch->batch_number} updated.");

        return back()->with('success', 'Production batch updated.');
    }

    public function destroy(ProductionBatch $productionBatch)
    {
        $num = $productionBatch->batch_number;
        $productionBatch->delete();

        ActivityLog::record('Production', 'Deleted Batch', "Batch {$num} deleted.");

        return back()->with('success', 'Production batch deleted.');
    }
}
