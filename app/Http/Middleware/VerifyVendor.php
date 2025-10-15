<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyVendor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !$user->is_vendor) {
            abort(403, 'Access denied. Vendor account required.');
        }

        if (!$user->has_active_subscription) {
            return redirect()->route('subscription.expired')
                ->with('error', 'Please activate your subscription to access vendor features.');
        }

        return $next($request);
    }
}