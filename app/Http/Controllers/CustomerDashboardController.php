<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
    /**
     * Display customer dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Statistics
        $stats = [
            'total_orders' => $user->orders()->count(),
            'pending_orders' => $user->orders()->whereIn('status', ['pending', 'confirmed', 'processing'])->count(),
            'completed_orders' => $user->orders()->where('status', 'delivered')->count(),
            'total_spent' => $user->orders()->where('payment_status', 'paid')->sum('total'),
        ];

        // Recent orders
        $recentOrders = $user->orders()
            ->with(['store', 'items'])
            ->latest()
            ->limit(5)
            ->get();

        return view('customer.dashboard', compact('user', 'stats', 'recentOrders'));
    }

    /**
     * Display customer orders
     */
    public function orders(Request $request)
    {
        $user = Auth::user();

        $query = $user->orders()->with(['store', 'items']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by order number
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $orders = $query->latest()->paginate(15);

        return view('customer.orders.index', compact('orders'));
    }

    /**
     * Show single order
     */
    public function showOrder(Order $order)
    {
        $user = Auth::user();

        // Verify order belongs to this user
        if ($order->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load(['store', 'items.product', 'items.variant']);

        return view('customer.orders.show', compact('order'));
    }

    /**
     * Cancel order
     */
    public function cancelOrder(Request $request, Order $order)
    {
        $user = Auth::user();

        // Verify order belongs to this user
        if ($order->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this order.');
        }

        if (!$order->canBeCancelled()) {
            return redirect()->back()
                ->with('error', 'This order cannot be cancelled.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $order->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->reason,
        ]);

        // TODO: Restore product stock
        // TODO: Notify vendor
        // event(new OrderCancelled($order));

        return redirect()->back()
            ->with('success', 'Order cancelled successfully.');
    }

    /**
     * Display wishlist
     */
    public function wishlist()
    {
        $user = Auth::user();
        
        // TODO: Implement wishlist functionality
        
        return view('customer.wishlist', compact('user'));
    }

    /**
     * Display reviews
     */
    public function reviews()
    {
        $user = Auth::user();
        
        // TODO: Implement reviews functionality
        
        return view('customer.reviews', compact('user'));
    }
}
