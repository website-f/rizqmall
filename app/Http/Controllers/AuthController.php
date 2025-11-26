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
     * Handle incoming SSO request from Sandbox (token-based)
     * Route: GET /auth/sso?token=xyz
     */
    public function handleSsoLogin(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        Log::info('SSO Login attempt', [
            'ip' => $request->ip(),
        ]);

        try {
            // Step 1: Validate token with Sandbox
            $sandboxService = app(\App\Services\SandboxApiService::class);
            $userData = $sandboxService->validateSsoToken($request->token);

            if (!$userData) {
                Log::error('SSO token validation failed');

                return redirect()->route('rizqmall.home')
                    ->with('error', 'Invalid or expired authentication token. Please login again.');
            }

            // Step 2: Create or update local user
            $user = User::updateOrCreate(
                ['subscription_user_id' => $userData['id']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'phone' => $userData['phone'] ?? null,
                    'avatar' => $userData['avatar'] ?? null,
                    'user_type' => $userData['user_type'],
                    'auth_type' => 'sso',
                    'is_active' => true,
                    'email_verified' => true,
                    'subscription_status' => $userData['subscription_status'],
                    'subscription_expires_at' => $userData['subscription_expires_at'] ? \Carbon\Carbon::parse($userData['subscription_expires_at']) : null,
                    'last_sync_at' => now(),
                ]
            );

            // Step 3: Check vendor subscription requirements
            if ($user->user_type === 'vendor') {
                if ($user->subscription_status !== 'active') {
                    Log::warning('Vendor subscription not active', [
                        'user_id' => $user->id,
                        'subscription_user_id' => $userData['id'],
                        'status' => $user->subscription_status,
                    ]);

                    return redirect()->route('subscription.expired')
                        ->with('error', 'Your vendor subscription is not active. Please renew your subscription.');
                }
            }

            // Step 4: Create user session
            $sessionId = Str::uuid();
            UserSession::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'session_id' => $sessionId,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'access_token' => $request->token,
                    'last_activity' => now(),
                    'expires_at' => now()->addDays(30),
                ]
            );

            // Step 5: Log the user in
            Auth::login($user, true);

            // Update last login
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Step 6: Merge guest cart if exists
            if (session()->has('guest_cart_id')) {
                $this->mergeGuestCart($user);
            }

            // Step 7: Store session data
            session([
                'subscription_user_id' => $userData['id'],
                'session_id' => $sessionId,
                'user_type' => $user->user_type,
                'stores_quota' => $userData['stores_quota'] ?? 0,
            ]);

            Log::info('User logged in successfully via SSO', [
                'user_id' => $user->id,
                'subscription_user_id' => $userData['id'],
                'user_type' => $user->user_type,
            ]);

            // Step 8: Redirect based on user type
            return $this->redirectAfterLogin($user);
        } catch (\Exception $e) {
            Log::error('SSO Login failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('rizqmall.home')
                ->with('error', 'An error occurred during authentication. Please try again.');
        }
    }

    /**
     * Merge guest cart into user cart
     */
    private function mergeGuestCart(User $user)
    {
        try {
            $guestCartId = session('guest_cart_id');
            $guestCart = \App\Models\Cart::where('session_id', $guestCartId)->first();

            if ($guestCart) {
                // Get or create user cart
                $userCart = $user->cart()->firstOrCreate([
                    'user_id' => $user->id,
                ]);

                // Move all items from guest cart to user cart
                foreach ($guestCart->items as $item) {
                    // Check if item already exists in user cart
                    $existingItem = $userCart->items()
                        ->where('product_id', $item->product_id)
                        ->where('variant_id', $item->variant_id)
                        ->first();

                    if ($existingItem) {
                        // Update quantity
                        $existingItem->update([
                            'quantity' => $existingItem->quantity + $item->quantity,
                        ]);
                    } else {
                        // Move item to user cart
                        $item->update(['cart_id' => $userCart->id]);
                    }
                }

                // Mark guest cart as merged and delete
                $guestCart->update(['merged_at' => now()]);
                $guestCart->delete();

                // Clear guest cart session
                session()->forget('guest_cart_id');

                Log::info('Guest cart merged successfully', [
                    'user_id' => $user->id,
                    'guest_cart_id' => $guestCartId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to merge guest cart', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
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

    /**
     * Show customer registration form
     * Route: GET /register
     */
    public function showRegisterForm()
    {
        // If already logged in, redirect to home
        if (Auth::check()) {
            return redirect()->route('rizqmall.home');
        }

        return view('auth.register');
    }

    /**
     * Handle customer registration
     * Route: POST /register
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ]);

        try {
            // Create new customer user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'user_type' => 'customer',
                'auth_type' => 'local',
                'is_active' => true,
                'email_verified' => false, // Will need to verify email
            ]);

            Log::info('New customer registered', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            // Create session
            $sessionId = Str::uuid();
            UserSession::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_activity' => now(),
                'expires_at' => now()->addDays(30),
            ]);

            // Log the user in
            Auth::login($user, true);

            // Update last login
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Store session data
            session([
                'session_id' => $sessionId,
                'user_type' => 'customer',
            ]);

            // Merge guest cart if exists
            if (session()->has('guest_cart_id')) {
                $this->mergeGuestCart($user);
            }

            return redirect()->route('rizqmall.home')
                ->with('success', 'Welcome to RizqMall, ' . $user->name . '! Your account has been created successfully.');
        } catch (\Exception $e) {
            Log::error('Customer registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Registration failed. Please try again.');
        }
    }
}
