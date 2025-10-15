<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyStoreOwner
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $storeId = $request->route('store');

        if (!$user || !$user->is_vendor) {
            abort(403, 'Access denied.');
        }

        // Check if user owns this store
        $store = $user->stores()->find($storeId);
        
        if (!$store) {
            abort(403, 'You do not have permission to access this store.');
        }

        // Share store with views
        view()->share('currentStore', $store);

        return $next($request);
    }
}