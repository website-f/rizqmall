<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'product_id', 'variant_id', 'quantity', 'price'];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function variant() {
        return $this->belongsTo(Variant::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
