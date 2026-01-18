<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'store_id',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'subtotal',
        'tax',
        'delivery_fee',
        'discount',
        'total',
        'shipping_address',
        'billing_address',
        'delivery_type',
        'tracking_number',
        'estimated_delivery',
        'delivered_at',
        'customer_notes',
        'vendor_notes',
        'cancellation_reason',
        'preferred_date',
        'preferred_time',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'estimated_delivery' => 'datetime',
        'delivered_at' => 'datetime',
        'preferred_date' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'confirmed' => 'info',
            'processing' => 'primary',
            'ready_for_pickup' => 'info',
            'out_for_delivery' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            'refunded' => 'secondary',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getFormattedShippingAddressAttribute()
    {
        if (!$this->shipping_address) {
            return 'N/A';
        }

        $addr = $this->shipping_address;
        return implode('<br>', array_filter([
            $addr['full_name'] ?? '',
            $addr['phone'] ?? '',
            $addr['address_line_1'] ?? '',
            $addr['address_line_2'] ?? '',
            implode(', ', array_filter([
                $addr['city'] ?? '',
                $addr['state'] ?? '',
                $addr['postal_code'] ?? '',
            ])),
            $addr['country'] ?? '',
        ]));
    }

    // Methods
    public static function generateOrderNumber()
    {
        do {
            $number = 'RM' . date('Ymd') . strtoupper(substr(uniqid(), -6));
        } while (self::where('order_number', $number)->exists());

        return $number;
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function canBeRefunded()
    {
        return $this->status === 'delivered' && 
               $this->delivered_at && 
               $this->delivered_at->diffInDays(now()) <= 7;
    }

    public function updateStatus($status, $notes = null)
    {
        $this->update([
            'status' => $status,
            'vendor_notes' => $notes ?? $this->vendor_notes,
        ]);

        if ($status === 'delivered') {
            $this->update(['delivered_at' => now()]);
        }

        // Fire event for notifications
        event(new \App\Events\OrderStatusUpdated($this));
    }
}