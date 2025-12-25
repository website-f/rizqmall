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

        // Calculate totals
        $subtotal = $cart->items->sum(function ($item) {
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

        return view('checkout.index', compact('cart', 'subtotal', 'shipping', 'tax', 'total', 'addresses'));
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

        // Group items by store to create separate orders
        $itemsByStore = $cartItems->groupBy('product.store_id');
        $orders = [];
        $totalAmount = 0;

        // Store Logic: create orders
        foreach ($itemsByStore as $storeId => $items) {
            $storeSubtotal = $items->sum(function ($item) {
                // Get price with fallbacks
                $price = $item->price ?? $item->variant->price ?? $item->product->sale_price ?? $item->product->regular_price ?? 0;
                return floatval($price) * intval($item->quantity);
            });

            // Ensure subtotal is a valid number
            $storeSubtotal = floatval($storeSubtotal) ?: 0;
            $tax = round($storeSubtotal * 0.06, 2);
            $total = round($storeSubtotal + $tax, 2);

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'store_id' => $storeId,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'toyyibpay', // Now using VARCHAR after migration
                'subtotal' => $storeSubtotal,
                'tax' => $tax,
                'delivery_fee' => 0,
                'total' => $total,
                'shipping_address' => $user->default_address ? $user->default_address->toArray() : [],
                'billing_address' => $user->default_address ? $user->default_address->toArray() : [],
            ]);

            foreach ($items as $item) {
                // Get the actual price from cart item or product
                $itemPrice = floatval($item->price ?? $item->variant->price ?? $item->product->sale_price ?? $item->product->regular_price ?? 0);
                $quantity = intval($item->quantity);
                $itemSubtotal = round($itemPrice * $quantity, 2);
                $itemTax = round($itemSubtotal * 0.06, 2);
                $itemTotal = round($itemSubtotal + $itemTax, 2);

                // Get product image
                $productImage = null;
                if ($item->product->images && $item->product->images->isNotEmpty()) {
                    $productImage = $item->product->images->first()->url ?? $item->product->images->first()->image_url ?? null;
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

        if ($status == 1) {
            // Payment successful
            Order::where('payment_reference', $billCode)->update([
                'payment_status' => 'paid',
                'status' => 'confirmed'
            ]);
            Log::info('Payment confirmed for bill: ' . $billCode);
        } else {
            // Payment failed or pending
            Order::where('payment_reference', $billCode)->update([
                'payment_status' => $status == 3 ? 'failed' : 'pending',
                'status' => $status == 3 ? 'cancelled' : 'pending'
            ]);
            Log::info('Payment status updated for bill: ' . $billCode . ' Status: ' . $status);
        }

        return response('OK');
    }

    public function success(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        // Check payment status from request params (ToyyibPay return)
        $statusId = $request->status_id;
        $billCode = $request->billcode;

        // Update order if status provided in return URL
        if ($billCode && $statusId) {
            if ($statusId == 1) {
                Order::where('payment_reference', $billCode)->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed'
                ]);
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
