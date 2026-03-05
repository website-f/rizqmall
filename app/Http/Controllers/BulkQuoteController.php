<?php

namespace App\Http\Controllers;

use App\Models\BulkQuote;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BulkQuoteController extends Controller
{
    /**
     * Buyer submits a quote request from the product page.
     */
    public function requestQuote(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'requested_quantity' => 'required|integer|min:1',
            'buyer_notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to request a quote.');
        }

        $product = Product::with('store')->findOrFail($request->product_id);

        if (!$product->allow_quote_request) {
            return back()->with('error', 'This product does not accept quote requests.');
        }

        // Check if user already has a pending quote for this product
        $existingQuote = BulkQuote::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->whereIn('status', [BulkQuote::STATUS_PENDING, BulkQuote::STATUS_QUOTED])
            ->first();

        if ($existingQuote) {
            return back()->with('error', 'You already have an active quote request for this product.');
        }

        BulkQuote::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'store_id' => $product->store_id,
            'requested_quantity' => $request->requested_quantity,
            'buyer_notes' => $request->buyer_notes,
            'status' => BulkQuote::STATUS_PENDING,
        ]);

        return back()->with('success', 'Your quote request has been submitted. The vendor will respond shortly.');
    }

    /**
     * Vendor views incoming quote requests for their store.
     */
    public function index()
    {
        $user = Auth::user();
        $store = $user->stores()->first();

        if (!$store) {
            return redirect()->route('vendor.dashboard')->with('error', 'No store found.');
        }

        $quotes = BulkQuote::with(['user', 'product'])
            ->forStore($store->id)
            ->latest()
            ->paginate(20);

        return view('vendor.bulk-quotes.index', compact('quotes', 'store'));
    }

    /**
     * Vendor views a single quote detail.
     */
    public function show($id)
    {
        $user = Auth::user();
        $store = $user->stores()->first();

        $quote = BulkQuote::with(['user', 'product'])
            ->forStore($store->id)
            ->findOrFail($id);

        return view('vendor.bulk-quotes.show', compact('quote', 'store'));
    }

    /**
     * Vendor submits a price response to the buyer's quote request.
     */
    public function respond(Request $request, $id)
    {
        $request->validate([
            'quoted_price' => 'required|numeric|min:0.01',
            'vendor_notes' => 'nullable|string|max:1000',
            'expires_in_days' => 'required|integer|min:1|max:30',
        ]);

        $user = Auth::user();
        $store = $user->stores()->first();

        $quote = BulkQuote::forStore($store->id)->findOrFail($id);

        if ($quote->status !== BulkQuote::STATUS_PENDING) {
            return back()->with('error', 'This quote has already been responded to.');
        }

        $quotedTotal = $request->quoted_price * $quote->requested_quantity;

        $quote->update([
            'quoted_price' => $request->quoted_price,
            'quoted_total' => $quotedTotal,
            'vendor_notes' => $request->vendor_notes,
            'status' => BulkQuote::STATUS_QUOTED,
            'quoted_at' => now(),
            'expires_at' => now()->addDays($request->expires_in_days),
        ]);

        return redirect()->route('vendor.bulk-quotes.index')
            ->with('success', 'Quote response sent to the buyer.');
    }

    /**
     * Buyer accepts the vendor's quote and creates an order.
     */
    public function accept($id)
    {
        $user = Auth::user();
        $quote = BulkQuote::with(['product', 'store'])
            ->forUser($user->id)
            ->findOrFail($id);

        if (!$quote->canBeAccepted()) {
            return back()->with('error', 'This quote cannot be accepted. It may have expired or been previously processed.');
        }

        // Create the order from the quote
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'user_id' => $user->id,
            'store_id' => $quote->store_id,
            'order_type' => 'quote',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => $quote->quoted_total,
            'tax' => round($quote->quoted_total * 0.06, 2),
            'delivery_fee' => 0,
            'discount' => 0,
            'total' => round($quote->quoted_total * 1.06, 2),
            'customer_notes' => 'Bulk quote order - ' . $quote->requested_quantity . ' units',
        ]);

        // Create order item
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $quote->product_id,
            'product_name' => $quote->product->name,
            'product_image' => $quote->product->images()->first()?->image_path,
            'quantity' => $quote->requested_quantity,
            'price' => $quote->quoted_price,
            'subtotal' => $quote->quoted_total,
        ]);

        // Update quote status
        $quote->update([
            'status' => BulkQuote::STATUS_ACCEPTED,
            'accepted_at' => now(),
            'order_id' => $order->id,
        ]);

        return redirect()->route('checkout.success', $order)
            ->with('success', 'Quote accepted! Your order has been created.');
    }

    /**
     * Vendor rejects a quote request (declines to quote).
     */
    public function reject($id)
    {
        $user = Auth::user();
        $store = $user->stores()->first();

        $quote = BulkQuote::forStore($store->id)->findOrFail($id);

        if ($quote->status !== BulkQuote::STATUS_PENDING) {
            return back()->with('error', 'This quote has already been processed.');
        }

        $quote->update(['status' => BulkQuote::STATUS_REJECTED]);

        return redirect()->route('vendor.bulk-quotes.index')
            ->with('success', 'Quote request rejected.');
    }

    /**
     * Buyer rejects a vendor's quoted price.
     */
    public function buyerReject($id)
    {
        $user = Auth::user();
        $quote = BulkQuote::forUser($user->id)->findOrFail($id);

        if (!in_array($quote->status, [BulkQuote::STATUS_QUOTED, BulkQuote::STATUS_PENDING])) {
            return back()->with('error', 'This quote cannot be rejected.');
        }

        $quote->update(['status' => BulkQuote::STATUS_REJECTED]);

        return back()->with('success', 'Quote rejected.');
    }

    /**
     * Buyer views their quote requests.
     */
    public function buyerQuotes()
    {
        $user = Auth::user();

        $quotes = BulkQuote::with(['product', 'store'])
            ->forUser($user->id)
            ->latest()
            ->paginate(20);

        return view('customer.bulk-quotes.index', compact('quotes'));
    }
}
