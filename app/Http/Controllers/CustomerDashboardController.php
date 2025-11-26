<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Wishlist;
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

        $wishlistItems = $user->wishlists()
            ->with(['product.store'])
            ->latest()
            ->paginate(12);

        return view('customer.wishlist', compact('user', 'wishlistItems'));
    }

    /**
     * Add product to wishlist
     */
    public function addToWishlist(Request $request, Product $product)
    {
        $user = Auth::user();

        // Check if already in wishlist
        $exists = $user->wishlists()->where('product_id', $product->id)->exists();

        if ($exists) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Product is already in your wishlist.'], 200);
            }
            return redirect()->back()->with('info', 'Product is already in your wishlist.');
        }

        $user->wishlists()->create([
            'product_id' => $product->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Product added to wishlist!'], 200);
        }

        return redirect()->back()->with('success', 'Product added to wishlist!');
    }

    /**
     * Remove product from wishlist
     */
    public function removeFromWishlist(Request $request, Wishlist $wishlist)
    {
        $user = Auth::user();

        // Verify wishlist belongs to this user
        if ($wishlist->user_id !== $user->id) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }
            abort(403, 'Unauthorized access.');
        }

        $wishlist->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Product removed from wishlist.'], 200);
        }

        return redirect()->back()->with('success', 'Product removed from wishlist.');
    }

    /**
     * Add all wishlist items to cart
     */
    public function addAllToCart()
    {
        $user = Auth::user();
        $wishlistItems = $user->wishlists()->with('product')->get();

        if ($wishlistItems->isEmpty()) {
            return redirect()->back()->with('error', 'Your wishlist is empty.');
        }

        $cart = $user->cart()->firstOrCreate(['user_id' => $user->id]);
        $addedCount = 0;

        foreach ($wishlistItems as $wishlistItem) {
            if ($wishlistItem->product && $wishlistItem->product->stock_quantity > 0) {
                // Check if already in cart
                $cartItem = $cart->items()->where('product_id', $wishlistItem->product_id)->first();

                if ($cartItem) {
                    $cartItem->increment('quantity');
                } else {
                    $cart->items()->create([
                        'product_id' => $wishlistItem->product_id,
                        'quantity' => 1,
                        'price' => $wishlistItem->product->sale_price ?? $wishlistItem->product->regular_price,
                    ]);
                }
                $addedCount++;
            }
        }

        return redirect()->route('cart.index')
            ->with('success', "{$addedCount} items added to cart!");
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
