<?php

// app/Models/ProductVariant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'price',
        'sale_price',
        'stock_quantity',
        'color_hex',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    /**
     * Get the product that the variant belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the specific options (e.g., Color: Red, Size: Large) for the variant.
     */
    public function options(): HasMany
    {
        return $this->hasMany(VariantOptionValue::class, 'variant_id');
    }

    /**
     * Get the images specific to this variant (e.g., the red shirt image).
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'variant_id');
    }
}
