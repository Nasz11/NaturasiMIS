<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $logs = ActivityLog::with('user')
            ->when($search, function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('logs.index', compact('logs', 'search'));
    }
}
