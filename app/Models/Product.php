<?php

// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $casts = [
        'is_trackable' => 'boolean',
        'is_fragile' => 'boolean',
        'is_biodegradable' => 'boolean',
        'is_frozen' => 'boolean',
        'expiry_date' => 'datetime',
        'regular_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    /**
     * Get the store that owns the Product.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the category that the Product belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Get the variants for the Product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the images for the Product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->whereNull('variant_id'); // Main product images only
    }
    
    /**
     * Get the attributes (specifications) for the Product.
     */
    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    /**
     * The tags that belong to the Product.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
