<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Get or create cart for current user/session
     */
    private function getCart()
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        } else {
            // Guest user - use session
            $sessionId = session()->getId();
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

            $requestedQuantity = (int) $request->quantity;
            $quantity = $product->type === 'service' ? 1 : $requestedQuantity;

            if ($product->type !== 'service') {
                if ($product->allow_bulk_order && $product->minimum_order_quantity && $quantity < $product->minimum_order_quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Minimum order quantity is ' . $product->minimum_order_quantity . '.',
                    ], 400);
                }

                if ($product->is_preorder && $product->preorder_limit && $quantity > $product->preorder_limit) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Preorder limit is ' . $product->preorder_limit . ' unit(s).',
                    ], 400);
                }
            }

            // Check stock
            if ($product->type !== 'service') {
                $availableStock = $variant ? $variant->stock_quantity : $product->stock_quantity;
                if ($availableStock < $quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock available',
                    ], 400);
                }
            }

            // Get cart
            $cart = $this->getCart();

            // Determine price
            if ($product->type === 'service') {
                $price = $product->booking_fee ?? $product->package_price ?? $product->sale_price ?? $product->regular_price;
            } else {
                if ($variant) {
                    $price = $variant->sale_price ?? $variant->price ?? $product->sale_price ?? $product->regular_price;
                } else {
                    $price = $product->sale_price ?? $product->regular_price;
                    if ($product->allow_bulk_order && $product->bulk_price && $product->bulk_quantity_threshold) {
                        if ($quantity >= $product->bulk_quantity_threshold) {
                            $price = $product->bulk_price;
                        }
                    }
                }
            }

            // Check if item already exists
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->where('variant_id', $variant ? $variant->id : null)
                ->first();

            if ($cartItem) {
                // Update quantity
                $newQuantity = $product->type === 'service'
                    ? 1
                    : ($cartItem->quantity + $quantity);

                if ($product->type !== 'service') {
                    if ($product->allow_bulk_order && $product->minimum_order_quantity && $newQuantity < $product->minimum_order_quantity) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Minimum order quantity is ' . $product->minimum_order_quantity . '.',
                        ], 400);
                    }

                    if ($product->is_preorder && $product->preorder_limit && $newQuantity > $product->preorder_limit) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Preorder limit is ' . $product->preorder_limit . ' unit(s).',
                        ], 400);
                    }
                }

                // Check stock again
                if ($product->type !== 'service') {
                    if ($availableStock < $newQuantity) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot add more items. Maximum stock: ' . $availableStock,
                        ], 400);
                    }
                }

                if ($product->type === 'service') {
                    $cartItem->update(['quantity' => 1, 'price' => $price]);
                } else {
                    $updatedPrice = $price;
                    if (!$variant) {
                        $updatedPrice = $product->sale_price ?? $product->regular_price;
                        if ($product->allow_bulk_order && $product->bulk_price && $product->bulk_quantity_threshold) {
                            if ($newQuantity >= $product->bulk_quantity_threshold) {
                                $updatedPrice = $product->bulk_price;
                            }
                        }
                    }
                    $cartItem->update(['quantity' => $newQuantity, 'price' => $updatedPrice]);
                }
            } else {
                // Create new cart item
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant ? $variant->id : null,
                    'quantity' => $quantity,
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

        // Service bookings are fixed to 1
        if ($cartItem->product->type === 'service') {
            $servicePrice = $cartItem->product->booking_fee ?? $cartItem->product->package_price ?? $cartItem->product->sale_price ?? $cartItem->product->regular_price;
            $cartItem->update(['quantity' => 1, 'price' => $servicePrice]);
        } else {
            if ($cartItem->product->allow_bulk_order && $cartItem->product->minimum_order_quantity && $request->quantity < $cartItem->product->minimum_order_quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum order quantity is ' . $cartItem->product->minimum_order_quantity . '.',
                ], 400);
            }

            if ($cartItem->product->is_preorder && $cartItem->product->preorder_limit && $request->quantity > $cartItem->product->preorder_limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Preorder limit is ' . $cartItem->product->preorder_limit . ' unit(s).',
                ], 400);
            }

            // Check stock
            $availableStock = $cartItem->variant
                ? $cartItem->variant->stock_quantity
                : $cartItem->product->stock_quantity;

            if ($availableStock < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock. Available: ' . $availableStock,
                ], 400);
            }

            $updatedPrice = $cartItem->price;
            if ($cartItem->variant) {
                $updatedPrice = $cartItem->variant->sale_price ?? $cartItem->variant->price ?? $cartItem->product->sale_price ?? $cartItem->product->regular_price;
            } else {
                $updatedPrice = $cartItem->product->sale_price ?? $cartItem->product->regular_price;
                if ($cartItem->product->allow_bulk_order && $cartItem->product->bulk_price && $cartItem->product->bulk_quantity_threshold) {
                    if ($request->quantity >= $cartItem->product->bulk_quantity_threshold) {
                        $updatedPrice = $cartItem->product->bulk_price;
                    }
                }
            }

            $cartItem->update(['quantity' => $request->quantity, 'price' => $updatedPrice]);
        }

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
