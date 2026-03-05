<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkQuote extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'store_id',
        'requested_quantity',
        'buyer_notes',
        'quoted_price',
        'quoted_total',
        'vendor_notes',
        'status',
        'quoted_at',
        'accepted_at',
        'expires_at',
        'order_id',
    ];

    protected $casts = [
        'quoted_price' => 'decimal:2',
        'quoted_total' => 'decimal:2',
        'quoted_at' => 'datetime',
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_QUOTED = 'quoted';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CONVERTED = 'converted';

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeQuoted($query)
    {
        return $query->where('status', self::STATUS_QUOTED);
    }

    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helpers
    public function canBeAccepted(): bool
    {
        return $this->status === self::STATUS_QUOTED && !$this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_QUOTED => 'info',
            self::STATUS_ACCEPTED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_EXPIRED => 'secondary',
            self::STATUS_CONVERTED => 'primary',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending Review',
            self::STATUS_QUOTED => 'Quote Sent',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_CONVERTED => 'Order Created',
            default => ucfirst($this->status),
        };
    }
}
