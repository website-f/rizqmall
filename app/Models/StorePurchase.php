<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorePurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'toyyibpay_bill_code',
        'payment_status',
        'amount',
        'store_slots_purchased',
        'payment_date',
        'expires_at',
        'payment_response',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'expires_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the purchase
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active purchases (paid and not expired)
     */
    public function scopeActive($query)
    {
        return $query->where('payment_status', 'paid')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Check if purchase is active
     */
    public function isActive()
    {
        return $this->payment_status === 'paid'
            && (is_null($this->expires_at) || $this->expires_at->isFuture());
    }
}
