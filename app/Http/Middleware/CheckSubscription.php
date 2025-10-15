<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SubscriptionService;

class CheckSubscription
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('auth.redirect')
                ->with('error', 'Please login to continue.');
        }

        // Check if subscription is still active (for vendors)
        if ($user->is_vendor) {
            if (!$user->has_active_subscription) {
                return redirect()->route('subscription.expired')
                    ->with('error', 'Your subscription has expired. Please renew to continue.');
            }

            // Verify with subscription system periodically
            $lastVerified = session('subscription_last_verified');
            if (!$lastVerified || $lastVerified->diffInMinutes(now()) > 15) {
                $status = $this->subscriptionService->verifySubscription($user->subscription_user_id);
                
                if (!$status || $status['status'] !== 'active') {
                    // Update local status
                    $user->update([
                        'subscription_status' => $status['status'] ?? 'expired',
                        'subscription_expires_at' => $status['expires_at'] ?? null,
                    ]);

                    Auth::logout();
                    
                    return redirect()->route('subscription.expired')
                        ->with('error', 'Your subscription is no longer active.');
                }

                session(['subscription_last_verified' => now()]);
            }
        }

        return $next($request);
    }
}
