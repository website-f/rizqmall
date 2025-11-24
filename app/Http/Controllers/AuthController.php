<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSession;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Handle incoming SSO request from subscription system
     * Route: GET /auth/redirect?user_id=123&email=user@example.com&token=xyz
     */
    public function handleSubscriptionRedirect(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'user_id' => 'required|integer',
            'email' => 'required|email',
            'name' => 'required|string',
            'token' => 'nullable|string',
            'customer' => 'nullable|string', // 'true' for customer-only access
        ]);

        $subscriptionUserId = $request->user_id;
        $email = $request->email;
        $name = $request->name;
        $isCustomerOnly = $request->customer === 'true';

        Log::info('SSO Login attempt', [
            'user_id' => $subscriptionUserId,
            'email' => $email,
            'customer_only' => $isCustomerOnly,
            'ip' => $request->ip(),
        ]);

        try {
            // Step 1: Fetch user data from subscription system API
            $userData = $this->subscriptionService->syncUser($subscriptionUserId);

            if (!$userData) {
                Log::error('Failed to sync user from subscription system', [
                    'user_id' => $subscriptionUserId
                ]);
                
                return redirect()->route('rizqmall.home')
                    ->with('error', 'Unable to authenticate. Please try again or contact support.');
            }

            // Step 2: Verify subscription status (only for vendors)
            $subscriptionStatus = $this->subscriptionService->verifySubscription($subscriptionUserId);
            
            // Update local subscription data
            if ($subscriptionStatus) {
                $userData->update([
                    'subscription_status' => $subscriptionStatus['status'],
                    'subscription_expires_at' => $subscriptionStatus['expires_at'],
                ]);
            }

            // Step 3: Check if vendor subscription is required and active
            if ($userData->user_type === 'vendor') {
                if (!$subscriptionStatus || $subscriptionStatus['status'] !== 'active') {
                    Log::warning('Vendor subscription not active', [
                        'user_id' => $subscriptionUserId,
                        'status' => $subscriptionStatus['status'] ?? 'unknown'
                    ]);

                    return redirect()->route('subscription.expired')
                        ->with('error', 'Your vendor subscription is not active. Please renew your subscription.');
                }
            }

            // Step 4: Create or update user session
            $sessionId = Str::uuid();
            UserSession::updateOrCreate(
                ['user_id' => $userData->id],
                [
                    'session_id' => $sessionId,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'access_token' => $request->token,
                    'last_activity' => now(),
                    'expires_at' => now()->addDays(30),
                ]
            );

            // Step 5: Log the user in using Laravel Auth
            Auth::login($userData, true); // true = remember me
            
            // Update last login timestamp
            $userData->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Step 6: Store additional session data
            session([
                'subscription_user_id' => $subscriptionUserId,
                'session_id' => $sessionId,
                'user_type' => $userData->user_type,
                'subscription_last_verified' => now(),
            ]);

            Log::info('User logged in successfully', [
                'user_id' => $userData->id,
                'subscription_user_id' => $subscriptionUserId,
                'user_type' => $userData->user_type,
            ]);

            // Step 7: Redirect based on user type and status
            return $this->redirectAfterLogin($userData);

        } catch (\Exception $e) {
            Log::error('SSO Login failed', [
                'user_id' => $subscriptionUserId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('rizqmall.home')
                ->with('error', 'An error occurred during authentication. Please try again.');
        }
    }

    /**
     * Determine where to redirect after successful login
     */
    private function redirectAfterLogin(User $user)
    {
        // Vendors
        if ($user->user_type === 'vendor') {
            // Check if vendor has a store
            if ($user->stores()->exists()) {
                return redirect()->route('vendor.dashboard')
                    ->with('success', 'Welcome back to your store, ' . $user->name . '!');
            }
            
            // New vendor without store - redirect to store setup
            return redirect()->route('store.select-category')
                ->with('info', 'Welcome! Let\'s set up your store to get started.');
        }

        // Customers - redirect to homepage
        return redirect()->route('rizqmall.home')
            ->with('success', 'Welcome to RizqMall, ' . $user->name . '!');
    }

    /**
     * Show login page (for direct RizqMall login)
     * Route: GET /login
     */
    public function showLoginForm()
    {
        // If already logged in, redirect to appropriate dashboard
        if (Auth::check()) {
            return redirect()->route('rizqmall.home');
        }

        return view('auth.login');
    }

    /**
     * Handle direct RizqMall login (alternative to SSO)
     * Route: POST /login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Check if user exists in local database
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'No account found. Please login through the subscription system first.');
        }

        // Verify with subscription system
        try {
            // Here you would validate credentials with subscription system
            // For now, we'll just check if user has active subscription
            $subscriptionStatus = $this->subscriptionService->verifySubscription($user->subscription_user_id);
            
            if (!$subscriptionStatus) {
                return back()->with('error', 'Unable to verify your account. Please try logging in through the subscription system.');
            }

            // For vendors, require active subscription
            if ($user->is_vendor && $subscriptionStatus['status'] !== 'active') {
                return redirect()->route('subscription.expired')
                    ->with('error', 'Your subscription has expired.');
            }

            // Log the user in
            Auth::login($user, $request->has('remember'));
            
            $user->updateLastLogin($request->ip());

            // Create session
            $sessionId = Str::uuid();
            UserSession::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'session_id' => $sessionId,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_activity' => now(),
                    'expires_at' => now()->addDays(30),
                ]
            );

            session([
                'subscription_user_id' => $user->subscription_user_id,
                'session_id' => $sessionId,
                'user_type' => $user->user_type,
            ]);

            return $this->redirectAfterLogin($user);

        } catch (\Exception $e) {
            Log::error('Direct login failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Login failed. Please try again.');
        }
    }

    /**
     * Logout
     * Route: POST /logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            Log::info('User logging out', [
                'user_id' => $user->id,
                'subscription_user_id' => $user->subscription_user_id,
            ]);

            // Delete user session
            UserSession::where('user_id', $user->id)->delete();
            
            // Clear cache
            $this->subscriptionService->clearUserCache($user->subscription_user_id);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Option 1: Redirect back to RizqMall home
        return redirect()->route('rizqmall.home')
            ->with('success', 'You have been logged out successfully.');

        // Option 2: Redirect back to subscription system
        // $subscriptionUrl = config('services.subscription.base_url');
        // return redirect($subscriptionUrl . '/logout');
    }

    /**
     * Show authentication error page
     */
    public function showError()
    {
        return view('auth.error');
    }

    /**
     * Show subscription expired page
     */
    public function subscriptionExpired()
    {
        return view('auth.subscription-expired');
    }

    /**
     * Redirect to subscription system for renewal
     */
    public function redirectToRenewal()
    {
        $subscriptionUrl = config('services.subscription.base_url');
        return redirect($subscriptionUrl . '/subscriptions/renew');
    }

    /**
     * Verify session is still valid (AJAX endpoint)
     * Route: GET /auth/verify-session
     */
    public function verifySession(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['valid' => false, 'message' => 'Not authenticated'], 401);
        }

        $session = UserSession::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->first();

        if (!$session) {
            Auth::logout();
            return response()->json(['valid' => false, 'message' => 'Session expired'], 401);
        }

        // Update last activity
        $session->update(['last_activity' => now()]);

        // Verify subscription is still active (for vendors)
        if ($user->is_vendor) {
            $subscriptionStatus = $this->subscriptionService->verifySubscription($user->subscription_user_id);
            
            if (!$subscriptionStatus || $subscriptionStatus['status'] !== 'active') {
                Auth::logout();
                return response()->json([
                    'valid' => false, 
                    'message' => 'Subscription expired',
                    'redirect' => route('subscription.expired')
                ], 403);
            }
        }

        return response()->json([
            'valid' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
            ]
        ]);
    }

    /**
     * Handle guest checkout
     */
    public function guestCheckout(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        // Create guest session
        $sessionId = Str::uuid();
        session([
            'guest_checkout' => true,
            'guest_name' => $request->name,
            'guest_email' => $request->email,
            'guest_phone' => $request->phone,
            'session_id' => $sessionId,
        ]);

        return redirect()->route('checkout.index')
            ->with('success', 'Continue with your purchase as guest.');
    }
}