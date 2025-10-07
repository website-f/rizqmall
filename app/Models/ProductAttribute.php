<?php

// app/Models/ProductAttribute.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttribute extends Model
{
    protected $fillable = [
        'name', 'slug', 'display_type', 'sort_order', 'is_visible'
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function options()
    {
        return $this->hasMany(AttributeOption::class, 'attribute_id')->orderBy('sort_order');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_pivot')
            ->withPivot('sort_order')
            ->withTimestamps();
    }
}