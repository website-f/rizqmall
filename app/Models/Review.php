<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'rating',
        'title',
        'comment',
        'images',
        'verified_purchase',
        'helpful_count',
        'is_approved',
    ];

    protected $casts = [
        'images' => 'array',
        'verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
        'helpful_count' => 'integer',
    ];

    /**
     * Get the user that wrote the review
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product being reviewed
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the order (if verified purchase)
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope to get only approved reviews
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope to get verified purchases
     */
    public function scopeVerified($query)
    {
        return $query->where('verified_purchase', true);
    }
}
