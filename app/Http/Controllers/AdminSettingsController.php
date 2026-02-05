<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function edit()
    {
        $taxRate = Setting::getFloat('tax_rate', config('rizqmall.tax_rate', 0.06));
        $shippingStandard = Setting::getFloat('shipping.standard', config('rizqmall.shipping.standard', 5.00));
        $shippingExpress = Setting::getFloat('shipping.express', config('rizqmall.shipping.express', 15.00));
        $shippingPickup = Setting::getFloat('shipping.pickup', config('rizqmall.shipping.pickup', 0.00));

        $taxRatePercent = $taxRate * 100;

        return view('admin.settings', compact(
            'taxRate',
            'taxRatePercent',
            'shippingStandard',
            'shippingExpress',
            'shippingPickup'
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'tax_rate_percent' => 'required|numeric|min:0|max:100',
            'shipping_standard' => 'required|numeric|min:0',
            'shipping_express' => 'required|numeric|min:0',
            'shipping_pickup' => 'required|numeric|min:0',
        ]);

        $taxRate = round(((float) $validated['tax_rate_percent']) / 100, 4);

        Setting::setValues([
            'tax_rate' => $taxRate,
            'shipping.standard' => round((float) $validated['shipping_standard'], 2),
            'shipping.express' => round((float) $validated['shipping_express'], 2),
            'shipping.pickup' => round((float) $validated['shipping_pickup'], 2),
        ]);

        return redirect()->back()->with('success', 'Platform pricing settings updated successfully.');
    }
}
