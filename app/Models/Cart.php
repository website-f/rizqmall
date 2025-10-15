<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        // ... other fields
    ]

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
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
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
        return $this->subtotal * 0.06;
    }

    /**
     * Calculate shipping (example logic)
     */
    public function getShippingAttribute(): float
    {
        // Free shipping above RM100
        if ($this->subtotal >= 100) {
            return 0;
        }
        return 10; // Flat rate RM10
    }

    /**
     * Get grand total
     */
    public function getGrandTotalAttribute(): float
    {
        return $this->subtotal + $this->tax + $this->shipping;
    }
}
