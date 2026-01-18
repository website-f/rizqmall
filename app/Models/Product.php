<?php

// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'product_category_id',
        'type',
        'product_type',
        'name',
        'slug',
        'short_description',
        'description',
        'regular_price',
        'sale_price',
        'cost_price',
        'sku',
        'stock_quantity',
        'low_stock_threshold',
        'track_inventory',
        'allow_backorder',
        // Marketplace/Bulk order fields
        'allow_bulk_order',
        'minimum_order_quantity',
        'bulk_price',
        'bulk_quantity_threshold',
        // Preorder fields
        'is_preorder',
        'preorder_release_date',
        'preorder_limit',
        'preorder_note',
        'lead_time_days',
        // Physical attributes
        'weight',
        'length',
        'width',
        'height',
        'is_fragile',
        'is_biodegradable',
        'is_frozen',
        'max_temperature',
        'requires_prescription',
        'expiry_date',
        'has_expiry',
        'service_duration',
        'service_availability',
        'service_days',
        'service_start_time',
        'service_end_time',
        'drug_code',
        'manufacturer',
        'active_ingredient',
        'dosage_form',
        'strength',
        'product_id_type',
        'product_id_value',
        'meta_title',
        'meta_description',
        'status'
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'bulk_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_fragile' => 'boolean',
        'is_biodegradable' => 'boolean',
        'is_frozen' => 'boolean',
        'requires_prescription' => 'boolean',
        'has_expiry' => 'boolean',
        'track_inventory' => 'boolean',
        'allow_backorder' => 'boolean',
        'allow_bulk_order' => 'boolean',
        'is_preorder' => 'boolean',
        'expiry_date' => 'date',
        'preorder_release_date' => 'date',
        'service_days' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(Str::random(8));
            }
        });
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function specifications()
    {
        return $this->hasMany(ProductSpecification::class)->orderBy('sort_order');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getPrimaryImageAttribute()
    {
        $image = $this->images()->where('is_primary', true)->first();
        return $image ? $image->url : asset('images/product-placeholder.png');
    }

    public function getPriceAttribute()
    {
        if ($this->sale_price && $this->sale_price > 0) {
            return $this->sale_price;
        }
        return $this->regular_price;
    }

    public function getOnSaleAttribute()
    {
        return $this->sale_price && $this->sale_price < $this->regular_price;
    }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->on_sale) return 0;
        return round((($this->regular_price - $this->sale_price) / $this->regular_price) * 100);
    }

    public function getRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Get the average rating from approved reviews
     * This provides dynamic rating_average based on actual customer reviews
     */
    public function getRatingAverageAttribute()
    {
        return (float) ($this->reviews()->avg('rating') ?? 0);
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    /**
     * Get the count of approved reviews
     * This provides dynamic rating_count based on actual customer reviews
     */
    public function getRatingCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function getImageUrlAttribute()
    {
        return $this->primary_image;
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeBulkAvailable($query)
    {
        return $query->where('allow_bulk_order', true);
    }

    public function scopePreorder($query)
    {
        return $query->where('is_preorder', true);
    }

    /**
     * Get the effective price based on quantity (for bulk orders)
     */
    public function getEffectivePrice($quantity = 1)
    {
        // If bulk order is enabled and quantity meets threshold
        if ($this->allow_bulk_order && $this->bulk_price && $this->bulk_quantity_threshold) {
            if ($quantity >= $this->bulk_quantity_threshold) {
                return $this->bulk_price;
            }
        }

        // Return sale price if available, otherwise regular price
        return $this->price;
    }

    /**
     * Check if quantity meets minimum order requirement
     */
    public function meetsMinimumOrder($quantity)
    {
        return $quantity >= ($this->minimum_order_quantity ?? 1);
    }

    /**
     * Check if this is a marketplace product (bulk/preorder enabled)
     */
    public function getIsMarketplaceProductAttribute()
    {
        return $this->allow_bulk_order || $this->is_preorder;
    }

    /**
     * Get availability status text
     */
    public function getAvailabilityStatusAttribute()
    {
        if ($this->is_preorder) {
            if ($this->preorder_release_date) {
                return 'Preorder - Available ' . $this->preorder_release_date->format('d M Y');
            }
            return 'Preorder';
        }

        if ($this->stock_quantity <= 0 && !$this->allow_backorder) {
            return 'Out of Stock';
        }

        if ($this->stock_quantity <= $this->low_stock_threshold) {
            return 'Low Stock';
        }

        return 'In Stock';
    }
}
