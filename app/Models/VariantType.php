<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantType extends Model
{
    protected $fillable = [
        'name', 'slug', 'display_type', 'has_image', 'sort_order'
    ];

    protected $casts = [
        'has_image' => 'boolean',
    ];

    public function options()
    {
        return $this->hasMany(VariantOption::class);
    }
}
