<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CartItem;

class CheckoutController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to proceed to checkout.');
        }

        // Get cart items
        $cartItems = CartItem::where('user_id', $user->id)
            ->with(['product.images', 'product.store', 'variant'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        // Calculate totals
        $subtotal = $cartItems->sum(function ($item) {
            $price = $item->variant
                ? ($item->variant->sale_price ?? $item->variant->price ?? $item->product->sale_price ?? $item->product->regular_price)
                : ($item->product->sale_price ?? $item->product->regular_price);
            return $price * $item->quantity;
        });

        $shipping = 0; // You can implement shipping calculation logic
        $tax = $subtotal * 0.06; // 6% tax
        $total = $subtotal + $shipping + $tax;

        // Get user addresses
        $addresses = $user->addresses()->get();

        return view('checkout.index', compact('cartItems', 'subtotal', 'shipping', 'tax', 'total', 'addresses'));
    }
}
