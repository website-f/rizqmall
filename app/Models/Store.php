<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Store extends Model
{
    protected $fillable = [
        'user_id',
        'store_category_id',
        'name',
        'slug',
        'image',
        'banner',
        'ssm_document',
        'ic_document',
        'business_registration_number',
        'phone',
        'email',
        'description',
        'location',
        'latitude',
        'longitude',
        'is_active',
        'is_verified',
        'business_registration_no',
        'tax_id'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($store) {
            if (empty($store->slug)) {
                $store->slug = Str::slug($store->name);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Add new relationships
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function category()
    {
        return $this->belongsTo(StoreCategory::class, 'store_category_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : asset('images/store-placeholder.png');
    }

    public function getBannerUrlAttribute()
    {
        return $this->banner ? asset('storage/' . $this->banner) : asset('images/banner-placeholder.png');
    }

    public function storeReviews()
    {
        return $this->hasMany(StoreReview::class);
    }

    public function getRatingAverageAttribute()
    {
        return (float) ($this->storeReviews()->avg('rating') ?? 0);
    }

    public function getRatingCountAttribute()
    {
        return $this->storeReviews()->count();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'store_followers', 'store_id', 'user_id');
    }
}
