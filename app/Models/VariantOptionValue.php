<?php

// app/Models/VariantOptionValue.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantOptionValue extends Model
{
    protected $fillable = [
        'variant_id',
        'option_name',
        'option_value',
    ];

    /**
     * Get the product variant that this option value belongs to.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}