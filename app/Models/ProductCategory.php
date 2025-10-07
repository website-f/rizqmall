<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = [
        'name', 'slug', 'parent_id', 'store_category_id', 'description',
        'image', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function storeCategory()
    {
        return $this->belongsTo(StoreCategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
