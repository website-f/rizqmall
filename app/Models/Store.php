<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends Model
{
    protected $fillable = [
        'auth_user_id', 'store_category_id', 'name', 'slug', 'image', 'banner',
        'phone', 'email', 'description', 'location', 'latitude', 'longitude',
        'is_active', 'is_verified'
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
}