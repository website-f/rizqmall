<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use App\Models\VendorMember;
use App\Services\VendorMemberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VendorMemberController extends Controller
{
    protected VendorMemberService $memberService;

    public function __construct(VendorMemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * Display the store members page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $store = $user->stores()->first();

        if (!$store) {
            return redirect()->route('vendor.dashboard')
                ->with('error', 'No store found.');
        }

        $query = VendorMember::with('customer')
            ->where('store_id', $store->id);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->active(); // Default to active members
        }

        // Join method filter
        if ($request->filled('join_method')) {
            $query->where('join_method', $request->join_method);
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('joined_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('joined_at', '<=', $request->to_date);
        }

        $members = $query->orderBy('joined_at', 'desc')->paginate(20);

        // Stats
        $stats = [
            'total_members' => $store->members()->active()->count(),
            'new_this_month' => $store->members()
                ->active()
                ->whereMonth('joined_at', now()->month)
                ->count(),
            'qr_scans' => $store->members()
                ->active()
                ->where('join_method', 'qr_scan')
                ->count(),
            'referrals' => $store->members()
                ->active()
                ->where('join_method', 'referral')
                ->count(),
        ];

        return view('vendor.members.index', compact('store', 'members', 'stats'));
    }

    /**
     * Join a store as a member directly from store page
     */
    public function joinStore(Request $request, Store $store)
    {
        // Check if user is logged in
        if (!Auth::check()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login or register to become a member.',
                    'redirect' => route('login', ['redirect' => url()->current()]),
                ], 401);
            }

            return redirect()->route('login')->with('info', 'Please login or register to become a member.');
        }

        $user = Auth::user();

        // Cannot join own store
        if ($store->user_id === $user->id) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot become a member of your own store.',
                ], 400);
            }

            return back()->with('error', 'You cannot become a member of your own store.');
        }

        // Vendors cannot become members of other stores
        if ($user->user_type === 'vendor') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendors cannot become members of other stores.',
                ], 400);
            }

            return back()->with('error', 'Vendors cannot become members of other stores.');
        }

        try {
            $result = $this->memberService->registerMember($store, $user, 'store_page');

            if ($result['success']) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'You have successfully joined as a member of ' . $store->name . '!',
                        'membership' => $result['membership'],
                    ]);
                }

                return back()->with('success', 'You have successfully joined as a member of ' . $store->name . '!');
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            Log::error('Error joining store', [
                'store_id' => $store->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred. Please try again.',
                ], 500);
            }

            return back()->with('error', 'An error occurred. Please try again.');
        }
    }

    /**
     * Join a store using referral code
     */
    public function joinByCode(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string|size:8',
        ]);

        // Check if user is logged in
        if (!Auth::check()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login or register to become a member.',
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()->route('login')->with('info', 'Please login or register to become a member.');
        }

        $user = Auth::user();
        $code = strtoupper($request->referral_code);

        // Find store by member referral code
        $store = Store::where('member_ref_code', $code)->first();

        if (!$store) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid referral code. Please check and try again.',
                ], 404);
            }

            return back()->with('error', 'Invalid referral code. Please check and try again.');
        }

        // Cannot join own store
        if ($store->user_id === $user->id) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot become a member of your own store.',
                ], 400);
            }

            return back()->with('error', 'You cannot become a member of your own store.');
        }

        // Vendors cannot become members
        if ($user->user_type === 'vendor') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendors cannot become members of other stores.',
                ], 400);
            }

            return back()->with('error', 'Vendors cannot become members of other stores.');
        }

        try {
            $result = $this->memberService->registerMember($store, $user, 'ref_code', $code);

            if ($result['success']) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'You have successfully joined as a member of ' . $store->name . '!',
                        'store' => [
                            'id' => $store->id,
                            'name' => $store->name,
                            'slug' => $store->slug,
                        ],
                        'membership' => $result['membership'],
                    ]);
                }

                return redirect()->route('store.profile', $store->slug)
                    ->with('success', 'You have successfully joined as a member of ' . $store->name . '!');
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            Log::error('Error joining store by code', [
                'code' => $code,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred. Please try again.',
                ], 500);
            }

            return back()->with('error', 'An error occurred. Please try again.');
        }
    }

    /**
     * Join a store via QR code scan (redirects to this route)
     */
    public function joinByQr(Request $request, string $code)
    {
        // Check if user is logged in
        if (!Auth::check()) {
            // Store the referral code in session and redirect to register
            session(['pending_vendor_member' => $code]);
            return redirect()->route('register')->with('info', 'Please register or login to complete your membership.');
        }

        $user = Auth::user();

        // Find store by member referral code
        $store = Store::where('member_ref_code', strtoupper($code))->first();

        if (!$store) {
            return redirect()->route('rizqmall.home')->with('error', 'Invalid referral code.');
        }

        // Cannot join own store
        if ($store->user_id === $user->id) {
            return redirect()->route('store.profile', $store->slug)
                ->with('error', 'You cannot become a member of your own store.');
        }

        // Vendors cannot become members
        if ($user->user_type === 'vendor') {
            return redirect()->route('store.profile', $store->slug)
                ->with('error', 'Vendors cannot become members of other stores.');
        }

        try {
            $result = $this->memberService->registerMember($store, $user, 'qr_scan', $code);

            if ($result['success']) {
                return redirect()->route('store.profile', $store->slug)
                    ->with('success', 'You have successfully joined as a member of ' . $store->name . '!');
            }

            return redirect()->route('store.profile', $store->slug)
                ->with('error', $result['message']);
        } catch (\Exception $e) {
            Log::error('Error joining store by QR', [
                'code' => $code,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('store.profile', $store->slug)
                ->with('error', 'An error occurred. Please try again.');
        }
    }

    /**
     * Get membership status for a store
     */
    public function getMembershipStatus(Store $store)
    {
        if (!Auth::check()) {
            return response()->json([
                'is_member' => false,
                'logged_in' => false,
            ]);
        }

        $user = Auth::user();
        $membership = VendorMember::where('store_id', $store->id)
            ->where('customer_id', $user->id)
            ->active()
            ->first();

        return response()->json([
            'is_member' => !is_null($membership),
            'logged_in' => true,
            'is_owner' => $store->user_id === $user->id,
            'is_vendor' => $user->user_type === 'vendor',
            'membership' => $membership ? [
                'joined_at' => $membership->joined_at->format('M d, Y'),
                'join_method' => $membership->join_method,
            ] : null,
        ]);
    }

    /**
     * Get store's member QR code URL
     */
    public function getQrCode(Store $store)
    {
        // Generate or get the store's member referral code
        if (!$store->member_ref_code) {
            $store->member_ref_code = $this->memberService->generateStoreRefCode();
            $store->save();
        }

        $joinUrl = route('vendor.member.join.qr', $store->member_ref_code);

        // Return the URL for frontend generation
        return response()->json([
            'url' => $joinUrl
        ]);
    }

    /**
     * Get member referral code for a store
     */
    public function getRefCode(Store $store)
    {
        // Check if the user is the store owner
        $user = Auth::user();
        if (!$user || $store->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Generate or get the store's member referral code
        if (!$store->member_ref_code) {
            $store->member_ref_code = $this->memberService->generateStoreRefCode();
            $store->save();
        }

        return response()->json([
            'success' => true,
            'ref_code' => $store->member_ref_code,
            'qr_url' => route('vendor.member.qr', $store), // This now returns JSON with URL
            'join_url' => route('vendor.member.join.qr', $store->member_ref_code),
        ]);
    }

    /**
     * Get list of members for a store (vendor dashboard)
     */
    public function getMembers(Request $request, Store $store)
    {
        $user = Auth::user();
        if (!$user || $store->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $members = VendorMember::with('customer')
            ->where('store_id', $store->id)
            ->active()
            ->orderBy('joined_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'members' => $members,
            'total_count' => $members->total(),
        ]);
    }

    /**
     * Get stores the current user is a member of
     */
    public function getMyMemberships()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to view your memberships.',
            ], 401);
        }

        $user = Auth::user();
        $memberships = VendorMember::with('store')
            ->where('customer_id', $user->id)
            ->active()
            ->orderBy('joined_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'memberships' => $memberships,
        ]);
    }
}
