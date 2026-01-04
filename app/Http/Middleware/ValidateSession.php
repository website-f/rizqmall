<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSession;

class ValidateSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip validation for auth routes to prevent redirect loops
        if ($request->routeIs('auth.*') ||
            $request->routeIs('login') ||
            $request->routeIs('register') ||
            $request->routeIs('customer.register.*') ||
            $request->routeIs('store.*') ||
            $request->routeIs('vendor.*') ||
            $request->routeIs('subscription.expired')) {
            return $next($request);
        }

        $sessionId = session('session_id');

        // If no session_id but user is authenticated (just logged in via SSO), skip validation
        if (!$sessionId && Auth::check()) {
            return $next($request);
        }

        if (!$sessionId) {
            return $next($request);
        }

        $userSession = UserSession::where('session_id', $sessionId)
            ->where('expires_at', '>', now())
            ->first();

        if (!$userSession) {
            session()->forget(['session_id', 'subscription_user_id', 'user_type']);
            Auth::logout();

            return redirect()->route('login')
                ->with('error', 'Your session has expired. Please login again.');
        }

        // Update last activity
        $userSession->update(['last_activity' => now()]);

        return $next($request);
    }
}