<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SandboxApiService;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    protected $sandboxService;

    public function __construct(SandboxApiService $sandboxService)
    {
        $this->sandboxService = $sandboxService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for these routes to prevent redirect loops
        if ($request->routeIs('subscription.expired') ||
            $request->routeIs('store.select-category') ||
            $request->routeIs('store.setup') ||
            $request->routeIs('store.store')) {
            return $next($request);
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to continue.');
        }

        // Only check subscription for vendors
        if ($user->user_type !== 'vendor') {
            return $next($request);
        }

        // Allow vendors to access store setup even without active subscription
        // They need to be able to create their store first
        return $next($request);
    }
}
