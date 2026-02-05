<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Setting;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        // ... other fields
    ];


    /**
     * Get the cart items
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get cart total
     */
    public function getTotalAttribute(): float
    {
        $this->loadMissing(['items.product', 'items.variant']);

        return $this->items->sum(function ($item) {
            if (!$item->product) {
                return 0;
            }

            $price = $item->price;
            if ($price === null && $item->variant) {
                $price = $item->variant->sale_price ?? $item->variant->price;
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
    }

    /**
     * Get cart item count
     */
    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get subtotal (before tax/shipping)
     */
    public function getSubtotalAttribute(): float
    {
        return $this->total;
    }

    /**
     * Calculate tax (example: 6% SST in Malaysia)
     */
    public function getTaxAttribute(): float
    {
        $taxRate = Setting::getFloat('tax_rate', config('rizqmall.tax_rate', 0.06));
        return $this->subtotal * $taxRate;
    }

    /**
     * Calculate shipping (example logic)
     */
    public function getShippingAttribute(): float
    {
        $this->loadMissing(['items.product']);

        $hasPhysicalItems = $this->items->contains(function ($item) {
            return $item->product && $item->product->type !== 'service';
        });

        $shippingStandard = Setting::getFloat('shipping.standard', config('rizqmall.shipping.standard', 5.00));
        $shippingPickup = Setting::getFloat('shipping.pickup', config('rizqmall.shipping.pickup', 0.00));

        return $hasPhysicalItems ? $shippingStandard : $shippingPickup;
    }

    /**
     * Get grand total
     */
    public function getGrandTotalAttribute(): float
    {
        return $this->subtotal + $this->tax + $this->shipping;
    }
}
