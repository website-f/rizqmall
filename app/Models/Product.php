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
        'store_id', 'product_category_id', 'type', 'product_type', 'name', 'slug',
        'short_description', 'description', 'regular_price', 'sale_price', 'cost_price',
        'sku', 'stock_quantity', 'low_stock_threshold', 'track_inventory', 'allow_backorder',
        'weight', 'length', 'width', 'height', 'is_fragile', 'is_biodegradable', 'is_frozen',
        'max_temperature', 'requires_prescription', 'expiry_date', 'has_expiry',
        'service_duration', 'service_availability', 'service_days', 'service_start_time',
        'service_end_time', 'drug_code', 'manufacturer', 'active_ingredient', 'dosage_form',
        'strength', 'product_id_type', 'product_id_value', 'meta_title', 'meta_description',
        'status'
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_fragile' => 'boolean',
        'is_biodegradable' => 'boolean',
        'is_frozen' => 'boolean',
        'requires_prescription' => 'boolean',
        'has_expiry' => 'boolean',
        'track_inventory' => 'boolean',
        'allow_backorder' => 'boolean',
        'expiry_date' => 'date',
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

    public function getPrimaryImageAttribute()
    {
        $image = $this->images()->where('is_primary', true)->first();
        return $image ? $image->url : asset('images/product-placeholder.png');
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

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
