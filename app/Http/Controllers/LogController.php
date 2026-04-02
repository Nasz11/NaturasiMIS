<?php
namespace App\Http\Controllers;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $search    = $request->get('search');
        $module    = $request->get('module');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $logs = ActivityLog::with('user')
            ->when($search, fn($q) => $q->where('username', 'like', "%{$search}%")
                ->orWhere('module', 'like', "%{$search}%")
                ->orWhere('action', 'like', "%{$search}%")
                ->orWhere('details', 'like', "%{$search}%"))
            ->when($module, fn($q) => $q->where('module', $module))
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends($request->query());

        $modules = ActivityLog::distinct()->pluck('module')->sort()->values();

        return view('logs.index', compact('logs', 'search', 'module', 'startDate', 'endDate', 'modules'));
    }
}