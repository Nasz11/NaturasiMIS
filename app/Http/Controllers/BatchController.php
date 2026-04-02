<?php
namespace App\Http\Controllers;

use App\Models\Batch;

class BatchController extends Controller
{
    public function index()
    {
        $batches = Batch::with('staff')
            ->orderByDesc('start_date')
            ->get();

        return view('batches.index', compact('batches'));
    }
}