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

        // Check subscription status
        if ($user->subscription_status !== 'active') {
            return redirect()->route('subscription.expired')
                ->with('error', 'Your subscription has expired. Please renew to continue.');
        }

        // Check if subscription is still valid (not expired)
        if ($user->subscription_expires_at && $user->subscription_expires_at->isPast()) {
            // Update status
            $user->subscription_status = 'expired';
            $user->save();

            return redirect()->route('subscription.expired')
                ->with('error', 'Your subscription has expired. Please renew to continue.');
        }

        return $next($request);
    }
}
