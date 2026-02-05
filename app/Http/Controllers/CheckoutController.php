<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;

class CheckoutController extends Controller
{
    /**
     * Get the current user's cart
     */
    private function getCart()
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }
        return Cart::where('user_id', $user->id)->first();
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to proceed to checkout.');
        }

        // Get cart through Cart model (user_id is on carts table, not cart_items)
        $cart = Cart::where('user_id', $user->id)
            ->with(['items.product.images', 'items.product.store', 'items.variant'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        // Determine cart composition
        $hasServiceItems = $cart->items->contains(function ($item) {
            return $item->product && $item->product->type === 'service';
        });
        $hasPhysicalItems = $cart->items->contains(function ($item) {
            return $item->product && $item->product->type !== 'service';
        });
        $hasMarketplaceItems = $cart->items->contains(function ($item) {
            $product = $item->product;
            if (!$product) {
                return false;
            }
            $storeCategory = $product->store?->category?->slug;
            return $product->is_marketplace_product || $storeCategory === 'marketplace';
        });

        $requiresSchedule = $hasServiceItems || $hasMarketplaceItems;

        $maxLeadTimeDays = 0;
        $latestPreorderDate = null;
        foreach ($cart->items as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }
            if ($product->lead_time_days && $product->lead_time_days > $maxLeadTimeDays) {
                $maxLeadTimeDays = (int) $product->lead_time_days;
            }
            if ($product->is_preorder && $product->preorder_release_date) {
                $releaseDate = \Carbon\Carbon::parse($product->preorder_release_date);
                if (!$latestPreorderDate || $releaseDate->gt($latestPreorderDate)) {
                    $latestPreorderDate = $releaseDate;
                }
            }
        }

        $baseMinDate = now()->addDay();
        $leadTimeDate = $maxLeadTimeDays > 0 ? now()->addDays($maxLeadTimeDays) : null;
        $minScheduleDate = $baseMinDate->copy();
        if ($leadTimeDate && $leadTimeDate->gt($minScheduleDate)) {
            $minScheduleDate = $leadTimeDate;
        }
        if ($latestPreorderDate && $latestPreorderDate->gt($minScheduleDate)) {
            $minScheduleDate = $latestPreorderDate;
        }

        $maxScheduleDate = now()->addDays(90);
        if ($minScheduleDate->gt($maxScheduleDate)) {
            $maxScheduleDate = $minScheduleDate->copy()->addDays(30);
        }

        // Calculate totals
        $subtotal = $cart->items->sum(function ($item) {
            if (!$item->product) {
                return 0;
            }

            $price = $item->price;
            if ($price === null) {
                if ($item->variant) {
                    $price = $item->variant->sale_price ?? $item->variant->price ?? null;
                }
            }

            if ($price === null) {
                if ($item->product->type === 'service') {
                    $price = $item->product->booking_fee
                        ?? $item->product->package_price
                        ?? $item->product->sale_price
                        ?? $item->product->regular_price;
                } else {
                    $price = $item->product->sale_price ?? $item->product->regular_price;
                }
            }

            return floatval($price ?? 0) * intval($item->quantity);
        });

        $taxRate = Setting::getFloat('tax_rate', config('rizqmall.tax_rate', 0.06));
        $shippingStandard = Setting::getFloat('shipping.standard', config('rizqmall.shipping.standard', 5.00));
        $shippingExpress = Setting::getFloat('shipping.express', config('rizqmall.shipping.express', 15.00));
        $shippingPickup = Setting::getFloat('shipping.pickup', config('rizqmall.shipping.pickup', 0.00));

        $shipping = $hasPhysicalItems ? $shippingStandard : $shippingPickup;
        $tax = $subtotal * $taxRate;
        $total = $subtotal + $shipping + $tax;

        // Get user addresses
        $addresses = $user->addresses()->get();

        return view('checkout.index', compact(
            'cart',
            'subtotal',
            'shipping',
            'tax',
            'total',
            'addresses',
            'hasServiceItems',
            'hasPhysicalItems',
            'hasMarketplaceItems',
            'requiresSchedule',
            'maxLeadTimeDays',
            'latestPreorderDate',
            'minScheduleDate',
            'maxScheduleDate',
            'taxRate',
            'shippingStandard',
            'shippingExpress',
            'shippingPickup'
        ));
    }

    public function process(Request $request)
    {
        $user = Auth::user();

        // Get cart through Cart model
        $cart = Cart::where('user_id', $user->id)
            ->with(['items.product.images', 'items.variant'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Cart is empty');
        }

        $cartItems = $cart->items;
        $hasServiceItems = $cartItems->contains(function ($item) {
            return $item->product && $item->product->type === 'service';
        });
        $hasPhysicalItems = $cartItems->contains(function ($item) {
            return $item->product && $item->product->type !== 'service';
        });
        $hasMarketplaceItems = $cartItems->contains(function ($item) {
            $product = $item->product;
            if (!$product) {
                return false;
            }
            $storeCategory = $product->store?->category?->slug;
            return $product->is_marketplace_product || $storeCategory === 'marketplace';
        });

        $requiresSchedule = $hasServiceItems || $hasMarketplaceItems;

        $taxRate = Setting::getFloat('tax_rate', config('rizqmall.tax_rate', 0.06));
        $shippingStandard = Setting::getFloat('shipping.standard', config('rizqmall.shipping.standard', 5.00));
        $shippingExpress = Setting::getFloat('shipping.express', config('rizqmall.shipping.express', 15.00));
        $shippingPickup = Setting::getFloat('shipping.pickup', config('rizqmall.shipping.pickup', 0.00));

        $maxLeadTimeDays = 0;
        $latestPreorderDate = null;
        foreach ($cartItems as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }
            if ($product->lead_time_days && $product->lead_time_days > $maxLeadTimeDays) {
                $maxLeadTimeDays = (int) $product->lead_time_days;
            }
            if ($product->is_preorder && $product->preorder_release_date) {
                $releaseDate = \Carbon\Carbon::parse($product->preorder_release_date);
                if (!$latestPreorderDate || $releaseDate->gt($latestPreorderDate)) {
                    $latestPreorderDate = $releaseDate;
                }
            }
        }

        $baseMinDate = now()->addDay();
        $leadTimeDate = $maxLeadTimeDays > 0 ? now()->addDays($maxLeadTimeDays) : null;
        $minScheduleDate = $baseMinDate->copy();
        if ($leadTimeDate && $leadTimeDate->gt($minScheduleDate)) {
            $minScheduleDate = $leadTimeDate;
        }
        if ($latestPreorderDate && $latestPreorderDate->gt($minScheduleDate)) {
            $minScheduleDate = $latestPreorderDate;
        }

        $maxScheduleDate = now()->addDays(90);
        if ($minScheduleDate->gt($maxScheduleDate)) {
            $maxScheduleDate = $minScheduleDate->copy()->addDays(30);
        }

        $rules = [];
        if ($requiresSchedule) {
            $rules['preferred_date'] = 'required|date|after_or_equal:' . $minScheduleDate->format('Y-m-d') . '|before_or_equal:' . $maxScheduleDate->format('Y-m-d');
            $rules['preferred_time'] = 'required|string';
        }
        if ($hasPhysicalItems) {
            $rules['shipping_method'] = 'required|in:standard,express,pickup';
        } else {
            $request->merge(['shipping_method' => 'pickup']);
        }

        $request->validate($rules);

        // Group items by store to create separate orders
        $itemsByStore = $cartItems->groupBy('product.store_id');
        $orders = [];
        $totalAmount = 0;

        // Calculate delivery fee based on shipping method
        $deliveryFee = 0;
        $deliveryType = $request->shipping_method;
        if ($hasPhysicalItems) {
            if ($deliveryType === 'standard') {
                $deliveryFee = $shippingStandard;
            } elseif ($deliveryType === 'express') {
                $deliveryFee = $shippingExpress;
            } elseif ($deliveryType === 'pickup') {
                $deliveryFee = $shippingPickup;
            }
        } else {
            $deliveryType = 'pickup';
            $deliveryFee = $shippingPickup;
        }

        // Store Logic: create orders
        foreach ($itemsByStore as $storeId => $items) {
            $storeHasPhysicalItems = $items->contains(function ($item) {
                return $item->product && $item->product->type !== 'service';
            });

            $storeDeliveryFee = $storeHasPhysicalItems ? $deliveryFee : 0;
            $storeDeliveryType = $storeHasPhysicalItems ? $deliveryType : 'pickup';

            $storeSubtotal = $items->sum(function ($item) {
                // Get price with fallbacks
                $price = $item->price ?? $item->variant->price ?? $item->product->sale_price ?? $item->product->regular_price ?? 0;
                return floatval($price) * intval($item->quantity);
            });

            // Ensure subtotal is a valid number
            $storeSubtotal = floatval($storeSubtotal) ?: 0;
            $tax = round($storeSubtotal * $taxRate, 2);
            $total = round($storeSubtotal + $tax + $storeDeliveryFee, 2);

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'store_id' => $storeId,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'toyyibpay', // Now using VARCHAR after migration
                'subtotal' => $storeSubtotal,
                'tax' => $tax,
                'delivery_fee' => $storeDeliveryFee,
                'delivery_type' => $storeDeliveryType,
                'total' => $total,
                'shipping_address' => $user->default_address ? $user->default_address->toArray() : [],
                'billing_address' => $user->default_address ? $user->default_address->toArray() : [],
                'preferred_date' => $request->preferred_date,
                'preferred_time' => $request->preferred_time,
                'customer_notes' => $request->notes,
            ]);

            foreach ($items as $item) {
                // Get the actual price from cart item or product
                $itemPrice = floatval($item->price ?? $item->variant->price ?? $item->product->sale_price ?? $item->product->regular_price ?? 0);
                $quantity = intval($item->quantity);
                $itemSubtotal = round($itemPrice * $quantity, 2);
                $itemTax = round($itemSubtotal * $taxRate, 2);
                $itemTotal = round($itemSubtotal + $itemTax, 2);

                // Get product image safely
                $productImage = null;
                if ($item->product && $item->product->images && $item->product->images->isNotEmpty()) {
                    $firstImage = $item->product->images->first();
                    $productImage = $firstImage->url ?? ($firstImage->path ? asset('storage/' . $firstImage->path) : null);
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->product->name ?? 'Unknown Product',
                    'variant_name' => $item->variant->name ?? null,
                    'sku' => $item->variant->sku ?? $item->product->sku ?? null,
                    'product_image' => $productImage,
                    'quantity' => $quantity,
                    'price' => $itemPrice,
                    'subtotal' => $itemSubtotal,
                    'tax' => $itemTax,
                    'total' => $itemTotal,
                ]);
            }

            $orders[] = $order;
            $totalAmount += $order->total;
        }

        // ToyyibPay Integration
        try {
            $billCode = $this->createToyyibPayBill($user, $totalAmount, $orders);
        } catch (\Exception $e) {
            Log::error('ToyyibPay Error: ' . $e->getMessage());
            return back()->with('error', 'Payment gateway error: ' . $e->getMessage());
        }

        if (!$billCode) {
            return back()->with('error', 'Payment initialization failed. Please try again.');
        }

        // Update orders with bill code
        foreach ($orders as $order) {
            $order->update(['payment_reference' => $billCode]);
        }

        // Clear Cart - delete the cart items
        $cart->items()->delete();

        // Redirect to ToyyibPay payment page
        $cfg = config('services.toyyibpay.rizqmall');
        $paymentUrl = rtrim($cfg['url'], '/') . '/' . $billCode;
        return redirect($paymentUrl);
    }

    private function createToyyibPayBill($user, $amount, $orders)
    {
        // Get configuration from config file
        $cfg = config('services.toyyibpay.rizqmall');

        if (!$cfg || empty($cfg['secret']) || empty($cfg['category'])) {
            Log::error('ToyyibPay configuration missing', [
                'secret_exists' => !empty($cfg['secret']),
                'category_exists' => !empty($cfg['category']),
                'url_exists' => !empty($cfg['url']),
            ]);
            throw new \Exception('Payment gateway not configured properly.');
        }

        // ToyyibPay expects amount in cents (sen)
        $amountInCents = (int) round($amount * 100);
        $orderRefs = implode(',', array_column(json_decode(json_encode($orders), true), 'order_number'));

        $baseUrl = rtrim($cfg['url'], '/');
        $apiUrl = $baseUrl . '/index.php/api/createBill';

        // Sanitize phone number (remove non-digits)
        $phone = preg_replace('/\D/', '', $user->phone ?? '0123456789');

        Log::info('Creating ToyyibPay bill', [
            'amount_in_cents' => $amountInCents,
            'amount_rm' => $amount,
            'orders' => $orderRefs,
            'api_url' => $apiUrl,
            'secret_prefix' => substr($cfg['secret'], 0, 8) . '...',
            'category' => $cfg['category'],
        ]);

        $response = Http::asForm()->post($apiUrl, [
            'userSecretKey' => $cfg['secret'],
            'categoryCode' => $cfg['category'],
            'billName' => 'RizqMall Order',
            'billDescription' => 'Orders: ' . $orderRefs,
            'billPriceSetting' => 1, // Fixed price
            'billPayorInfo' => 1, // Require payor info
            'billAmount' => $amountInCents,
            'billReturnUrl' => route('checkout.success', ['order' => $orders[0]->id]),
            'billCallbackUrl' => route('checkout.callback'),
            'billExternalReferenceNo' => 'RIZQMALL-' . $orders[0]->order_number,
            'billTo' => $user->name ?? 'Customer',
            'billEmail' => $user->email ?? '',
            'billPhone' => $phone,
            'billSplitPayment' => 0,
            'billSplitPaymentArgs' => '',
            'billPaymentChannel' => 0, // All payment channels (FPX, Card, etc)
            'billContentEmail' => 'Thank you for completing the payment for your RizqMall order.',
            'billChargeToCustomer' => 1, // Charge payment fees to customer
        ]);

        $data = $response->json();

        Log::info('ToyyibPay Response', ['response' => $data, 'status' => $response->status()]);

        // Check for error in response
        if ($response->failed()) {
            Log::error('ToyyibPay API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Payment gateway request failed.');
        }

        // Check for bill code in response (array format)
        if (is_array($data) && isset($data[0]['BillCode'])) {
            return $data[0]['BillCode'];
        }

        // Check alternate response format (object format)
        if (is_array($data) && isset($data['BillCode'])) {
            return $data['BillCode'];
        }

        // Log error if no bill code found
        Log::error('ToyyibPay failed to create bill', ['response' => $data]);

        throw new \Exception('Failed to create payment bill: ' . json_encode($data));
    }

    public function callback(Request $request)
    {
        // Log the callback for debugging
        Log::info('ToyyibPay Callback received', $request->all());

        $billCode = $request->billcode ?? $request->refno;
        $status = $request->status_id ?? $request->status; // 1 = success, 2 = pending, 3 = failed

        if (!$billCode) {
            Log::error('ToyyibPay callback missing billcode');
            return response('Missing billcode', 400);
        }

        // Get orders for this payment
        $orders = Order::where('payment_reference', $billCode)->get();

        if ($orders->isEmpty()) {
            Log::error('No orders found for billcode: ' . $billCode);
            return response('Orders not found', 404);
        }

        if ($status == 1) {
            // Payment successful - only process if not already paid
            $firstOrder = $orders->first();
            if ($firstOrder->payment_status !== 'paid') {
                // Update order status
                Order::where('payment_reference', $billCode)->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed'
                ]);

                // Decrement stock for each order item
                $this->decrementStockForOrders($orders);

                // Clear user's cart (extra safety measure)
                $userId = $firstOrder->user_id;
                $cart = Cart::where('user_id', $userId)->first();
                if ($cart) {
                    $cart->items()->delete();
                }

                Log::info('Payment confirmed and stock decremented for bill: ' . $billCode);
            }
        } elseif ($status == 3) {
            // Payment failed - restore stock if it was decremented
            Order::where('payment_reference', $billCode)->update([
                'payment_status' => 'failed',
                'status' => 'cancelled'
            ]);
            Log::info('Payment failed for bill: ' . $billCode);
        } else {
            // Payment pending
            Order::where('payment_reference', $billCode)->update([
                'payment_status' => 'pending',
                'status' => 'pending'
            ]);
            Log::info('Payment pending for bill: ' . $billCode);
        }

        return response('OK');
    }

    /**
     * Decrement stock for all items in the given orders
     */
    private function decrementStockForOrders($orders)
    {
        foreach ($orders as $order) {
            $order->load('items');
            foreach ($order->items as $item) {
                // Decrement product variant stock if applicable
                if ($item->variant_id) {
                    ProductVariant::where('id', $item->variant_id)
                        ->where('stock_quantity', '>=', $item->quantity)
                        ->decrement('stock_quantity', $item->quantity);
                }

                // Decrement main product stock
                if ($item->product_id) {
                    Product::where('id', $item->product_id)
                        ->where('stock_quantity', '>=', $item->quantity)
                        ->decrement('stock_quantity', $item->quantity);
                }
            }
        }
    }

    public function success(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        // Check payment status from request params (ToyyibPay return)
        $statusId = $request->status_id;
        $billCode = $request->billcode;

        // Update order if status provided in return URL (fallback if callback is slow)
        if ($billCode && $statusId == 1) {
            // Only process if not already paid (prevent double processing)
            if ($order->payment_status !== 'paid') {
                $orders = Order::where('payment_reference', $billCode)->get();

                Order::where('payment_reference', $billCode)->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed'
                ]);

                // Decrement stock (if callback hasn't done it already)
                $this->decrementStockForOrders($orders);

                // Clear user's cart
                $cart = Cart::where('user_id', $order->user_id)->first();
                if ($cart) {
                    $cart->items()->delete();
                }

                Log::info('Payment confirmed via success page for bill: ' . $billCode);
            }
            // Reload the order to get updated status
            $order->refresh();
        }

        // Fetch other orders with same payment_ref
        $relatedOrders = Order::where('payment_reference', $order->payment_reference)
            ->with(['items', 'store'])
            ->get();

        return view('checkout.success', compact('order', 'relatedOrders'));
    }
}
