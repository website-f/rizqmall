<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantOption extends Model
{
    protected $fillable = [
        'variant_id', 'variant_type_id', 'value', 'color_code', 'image', 'sort_order'
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function type()
    {
        return $this->belongsTo(VariantType::class, 'variant_type_id');
    }
}
