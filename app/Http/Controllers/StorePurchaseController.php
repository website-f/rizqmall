<?php

namespace App\Http\Controllers;

use App\Models\StorePurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StorePurchaseController extends Controller
{
    /**
     * Initiate store slot purchase
     */
    public function purchase(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to continue.');
        }

        $request->validate([
            'slots' => 'required|integer|min:1|max:10',
        ]);

        $slots = $request->slots;
        $basePrice = 2000; // RM20.00 per store slot in sen (cents)
        $baseTotalPrice = $slots * $basePrice; // Total base price in sen

        // Calculate tax (8%) and FPX charge (RM1.00)
        $tax = (int) round($baseTotalPrice * 0.08);
        $fpx = 100; // RM1.00 in sen
        $finalPrice = $baseTotalPrice + $tax + $fpx;

        // Convert back to RM for storage
        $finalPriceRM = $finalPrice / 100;

        // Create purchase record
        $purchase = StorePurchase::create([
            'user_id' => $user->id,
            'amount' => $finalPriceRM,
            'store_slots_purchased' => $slots,
            'payment_status' => 'pending',
            'expires_at' => now()->addMonth(), // Monthly subscription
        ]);

        try {
            $billCode = $this->createToyyibPayBill($user, $finalPrice, $purchase, $slots);

            if (!$billCode) {
                return redirect()->route('vendor.my-stores')
                    ->with('error', 'Payment gateway error. Please try again.');
            }

            // Update purchase with bill code
            $purchase->update(['toyyibpay_bill_code' => $billCode]);

            // Redirect to payment page
            $cfg = config('services.toyyibpay.rizqmall');
            $paymentUrl = rtrim($cfg['url'], '/') . '/' . $billCode;

            return redirect($paymentUrl);
        } catch (\Exception $e) {
            Log::error('Store purchase error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return redirect()->route('vendor.my-stores')
                ->with('error', 'An error occurred. Please try again.');
        }
    }

    /**
     * Create ToyyibPay bill for store purchase
     */
    private function createToyyibPayBill($user, $amountInCents, $purchase, $slots)
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

        $baseUrl = rtrim($cfg['url'], '/');
        $apiUrl = $baseUrl . '/index.php/api/createBill';

        // Sanitize phone number (remove non-digits)
        $phone = preg_replace('/\D/', '', $user->phone ?? $user->profile->phone_number ?? '0123456789');

        $billName = "RizqMall - {$slots} Additional Store Slot(s)";
        $billDescription = "Purchase {$slots} additional store slot(s) at RM20/month each";

        Log::info('Creating ToyyibPay bill for store purchase', [
            'amount_in_cents' => $amountInCents,
            'amount_rm' => $amountInCents / 100,
            'slots' => $slots,
            'purchase_id' => $purchase->id,
            'api_url' => $apiUrl,
            'secret_prefix' => substr($cfg['secret'], 0, 8) . '...',
            'category' => $cfg['category'],
        ]);

        $response = Http::asForm()->post($apiUrl, [
            'userSecretKey' => $cfg['secret'],
            'categoryCode' => $cfg['category'],
            'billName' => $billName,
            'billDescription' => $billDescription,
            'billPriceSetting' => 1, // Fixed price
            'billPayorInfo' => 1, // Require payor info
            'billAmount' => $amountInCents,
            'billReturnUrl' => route('vendor.my-stores'),
            'billCallbackUrl' => route('store-purchase.callback'),
            'billExternalReferenceNo' => 'STORE-PURCHASE-' . $purchase->id,
            'billTo' => $user->name ?? 'Customer',
            'billEmail' => $user->email ?? '',
            'billPhone' => $phone,
            'billSplitPayment' => 0,
            'billSplitPaymentArgs' => '',
            'billPaymentChannel' => 0, // All payment channels (FPX, Card, etc)
            'billContentEmail' => 'Thank you for purchasing additional store slots on RizqMall.',
            'billChargeToCustomer' => 1, // Charge payment fees to customer
        ]);

        $data = $response->json();

        Log::info('ToyyibPay Response for store purchase', ['response' => $data, 'status' => $response->status()]);

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

        Log::error('ToyyibPay bill code not found in response', ['response' => $data]);
        return null;
    }

    /**
     * Handle ToyyibPay callback
     */
    public function callback(Request $request)
    {
        Log::info('Store purchase callback received', $request->all());

        $billCode = $request->billcode ?? $request->billCode;
        $statusId = $request->status_id ?? $request->status ?? 0;

        if (!$billCode) {
            return redirect()->route('vendor.my-stores')
                ->with('error', 'Invalid payment response.');
        }

        // Find purchase by bill code
        $purchase = StorePurchase::where('toyyibpay_bill_code', $billCode)->first();

        if (!$purchase) {
            Log::error('Store purchase not found', ['bill_code' => $billCode]);

            return redirect()->route('vendor.my-stores')
                ->with('error', 'Purchase record not found.');
        }

        // Update purchase based on payment status
        if ($statusId == 1) {
            // Payment successful
            $purchase->update([
                'payment_status' => 'paid',
                'payment_date' => now(),
                'payment_response' => json_encode($request->all()),
            ]);

            Log::info('Store purchase payment successful', [
                'purchase_id' => $purchase->id,
                'user_id' => $purchase->user_id,
                'slots' => $purchase->store_slots_purchased,
            ]);

            return redirect()->route('vendor.my-stores')
                ->with('success', "Payment successful! You now have {$purchase->store_slots_purchased} additional store slot(s).");
        } else {
            // Payment failed or pending
            $purchase->update([
                'payment_status' => 'failed',
                'payment_response' => json_encode($request->all()),
            ]);

            Log::warning('Store purchase payment failed', [
                'purchase_id' => $purchase->id,
                'status_id' => $statusId,
            ]);

            return redirect()->route('vendor.my-stores')
                ->with('error', 'Payment failed. Please try again.');
        }
    }
}
