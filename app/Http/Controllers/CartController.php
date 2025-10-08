<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Get or create cart for current user/session
     */
    private function getCart()
    {
        $authUserId = session('auth_user_id');
        $sessionId = session()->getId();

        if ($authUserId) {
            $cart = Cart::firstOrCreate(['auth_user_id' => $authUserId]);
        } else {
            $cart = Cart::firstOrCreate(['session_id' => $sessionId]);
        }

        return $cart;
    }

    /**
     * Display cart page
     */
    public function index()
    {
        $cart = $this->getCart();
        $cart->load(['items.product.images', 'items.product.store', 'items.variant']);

        return view('store.cart', compact('cart'));
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($request->product_id);
            $variant = $request->variant_id ? ProductVariant::findOrFail($request->variant_id) : null;

            // Check stock
            if ($product->type !== 'service') {
                $availableStock = $variant ? $variant->stock_quantity : $product->stock_quantity;
                if ($availableStock < $request->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock available',
                    ], 400);
                }
            }

            // Get cart
            $cart = $this->getCart();

            // Determine price
            $price = $variant && $variant->sale_price 
                ? $variant->sale_price 
                : ($variant && $variant->price 
                    ? $variant->price 
                    : ($product->sale_price ?? $product->regular_price));

            // Check if item already exists
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->where('variant_id', $variant ? $variant->id : null)
                ->first();

            if ($cartItem) {
                // Update quantity
                $newQuantity = $cartItem->quantity + $request->quantity;
                
                // Check stock again
                if ($product->type !== 'service') {
                    if ($availableStock < $newQuantity) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot add more items. Maximum stock: ' . $availableStock,
                        ], 400);
                    }
                }
                
                $cartItem->update(['quantity' => $newQuantity]);
            } else {
                // Create new cart item
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant ? $variant->id : null,
                    'quantity' => $request->quantity,
                    'price' => $price,
                ]);
            }

            DB::commit();

            // Reload cart with items
            $cart->load('items');

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'cart_count' => $cart->item_count,
                'cart_total' => number_format($cart->total, 2),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        // Check if item belongs to user's cart
        $cart = $this->getCart();
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check stock
        if ($cartItem->product->type !== 'service') {
            $availableStock = $cartItem->variant 
                ? $cartItem->variant->stock_quantity 
                : $cartItem->product->stock_quantity;

            if ($availableStock < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock. Available: ' . $availableStock,
                ], 400);
            }
        }

        $cartItem->update(['quantity' => $request->quantity]);

        // Reload cart
        $cart->load('items');

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'line_total' => number_format($cartItem->line_total, 2),
            'cart_total' => number_format($cart->total, 2),
            'cart_count' => $cart->item_count,
            'subtotal' => number_format($cart->subtotal, 2),
            'tax' => number_format($cart->tax, 2),
            'shipping' => number_format($cart->shipping, 2),
            'grand_total' => number_format($cart->grand_total, 2),
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove(CartItem $cartItem)
    {
        // Check if item belongs to user's cart
        $cart = $this->getCart();
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $cartItem->delete();

        // Reload cart
        $cart->load('items');

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart_count' => $cart->item_count,
            'cart_total' => number_format($cart->total, 2),
            'subtotal' => number_format($cart->subtotal, 2),
            'tax' => number_format($cart->tax, 2),
            'shipping' => number_format($cart->shipping, 2),
            'grand_total' => number_format($cart->grand_total, 2),
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clear()
    {
        $cart = $this->getCart();
        $cart->items()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
        ]);
    }

    /**
     * Get cart count (for navbar)
     */
    public function count()
    {
        $cart = $this->getCart();
        return response()->json([
            'count' => $cart->item_count,
        ]);
    }
}