<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!empty($roles) && !in_array($user->role, $roles)) {
            abort(403, "You don't have permission to access this page.");
        }

        return $next($request);
    }
}

// ─────────────────────────────────────────────
// CheckPermission Middleware
// ─────────────────────────────────────────────
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): mixed
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->can($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => "You don't have permission for this action."], 403);
            }
            abort(403, "You don't have permission for this action.");
        }

        return $next($request);
    }
}
