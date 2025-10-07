<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSpecification extends Model
{
     protected $fillable = [
        'product_id', 'spec_key', 'spec_value', 'spec_group', 'sort_order'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
