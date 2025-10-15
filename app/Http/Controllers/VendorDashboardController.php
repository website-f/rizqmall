<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorDashboardController extends Controller
{
    /**
     * Display vendor dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $store = $user->stores()->first();

        if (!$store) {
            return redirect()->route('store.select-category')
                ->with('info', 'Please set up your store first.');
        }

        // Statistics
        $stats = [
            'total_products' => $store->products()->count(),
            'active_products' => $store->products()->where('status', 'published')->count(),
            'total_orders' => $store->orders()->count(),
            'pending_orders' => $store->orders()->pending()->count(),
            'total_revenue' => $store->orders()
                ->where('payment_status', 'paid')
                ->sum('total'),
            'monthly_revenue' => $store->orders()
                ->where('payment_status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->sum('total'),
        ];

        // Recent orders
        $recentOrders = $store->orders()
            ->with(['user', 'items'])
            ->latest()
            ->limit(10)
            ->get();

        // Top selling products
        $topProducts = Product::forStore($store->id)
            ->where('status', 'published')
            ->orderBy('sold_count', 'desc')
            ->limit(5)
            ->get();

        // Revenue chart data (last 30 days)
        $revenueChart = $store->orders()
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('vendor.dashboard', compact(
            'store',
            'stats',
            'recentOrders',
            'topProducts',
            'revenueChart'
        ));
    }

    /**
     * Display vendor orders
     */
    public function orders(Request $request)
    {
        $user = Auth::user();
        $store = $user->stores()->first();

        $query = $store->orders()->with(['user', 'items']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search by order number or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->latest()->paginate(20);

        return view('vendor.orders.index', compact('orders', 'store'));
    }

    /**
     * Show single order
     */
    public function showOrder(Order $order)
    {
        $user = Auth::user();
        $store = $user->stores()->first();

        // Verify order belongs to this store
        if ($order->store_id !== $store->id) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load(['user', 'items.product', 'items.variant']);

        return view('vendor.orders.show', compact('order', 'store'));
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, Order $order)
    {
        $user = Auth::user();
        $store = $user->stores()->first();

        // Verify order belongs to this store
        if ($order->store_id !== $store->id) {
            abort(403, 'Unauthorized access to this order.');
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,ready_for_pickup,out_for_delivery,delivered,cancelled',
            'notes' => 'nullable|string|max:1000',
            'tracking_number' => 'nullable|string|max:255',
        ]);

        $order->update([
            'status' => $request->status,
            'vendor_notes' => $request->notes,
            'tracking_number' => $request->tracking_number,
        ]);

        if ($request->status === 'delivered') {
            $order->update(['delivered_at' => now()]);
        }

        // TODO: Send notification to customer
        // event(new OrderStatusUpdated($order));

        return redirect()->back()
            ->with('success', 'Order status updated successfully!');
    }

    /**
     * Show analytics page
     */
    public function analytics()
    {
        $user = Auth::user();
        $store = $user->stores()->first();

        // Sales analytics
        $salesData = [
            'today' => $store->orders()
                ->where('payment_status', 'paid')
                ->whereDate('created_at', today())
                ->sum('total'),
            'week' => $store->orders()
                ->where('payment_status', 'paid')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('total'),
            'month' => $store->orders()
                ->where('payment_status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->sum('total'),
            'year' => $store->orders()
                ->where('payment_status', 'paid')
                ->whereYear('created_at', now()->year)
                ->sum('total'),
        ];

        // Order statistics
        $orderStats = [
            'total_orders' => $store->orders()->count(),
            'completed_orders' => $store->orders()->where('status', 'delivered')->count(),
            'pending_orders' => $store->orders()->whereIn('status', ['pending', 'confirmed'])->count(),
            'cancelled_orders' => $store->orders()->where('status', 'cancelled')->count(),
        ];

        // Top customers
        $topCustomers = $store->orders()
            ->select('user_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total) as total_spent'))
            ->where('payment_status', 'paid')
            ->groupBy('user_id')
            ->orderBy('total_spent', 'desc')
            ->with('user')
            ->limit(10)
            ->get();

        // Product performance
        $productPerformance = Product::forStore($store->id)
            ->select('products.*')
            ->withCount(['orderItems as total_sold' => function($query) {
                $query->select(DB::raw('SUM(quantity)'));
            }])
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        // Monthly revenue trend (last 12 months)
        $monthlyRevenue = $store->orders()
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('vendor.analytics', compact(
            'store',
            'salesData',
            'orderStats',
            'topCustomers',
            'productPerformance',
            'monthlyRevenue'
        ));
    }

    /**
     * Show settings page
     */
    public function settings()
    {
        $user = Auth::user();
        $store = $user->stores()->first();

        return view('vendor.settings', compact('store'));
    }

    /**
     * Update store settings
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $store = $user->stores()->first();

        $request->validate([
            'allow_cod' => 'nullable|boolean',
            'allow_online_payment' => 'nullable|boolean',
            'minimum_order' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'free_delivery_threshold' => 'nullable|numeric|min:0',
            'operating_hours' => 'nullable|array',
            'auto_accept_orders' => 'nullable|boolean',
        ]);

        $store->update([
            'allow_cod' => $request->has('allow_cod'),
            'allow_online_payment' => $request->has('allow_online_payment'),
            'minimum_order' => $request->minimum_order,
            'delivery_fee' => $request->delivery_fee,
            'free_delivery_threshold' => $request->free_delivery_threshold,
            'operating_hours' => $request->operating_hours,
        ]);

        return redirect()->back()
            ->with('success', 'Settings updated successfully!');
    }
}