<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends Model
{
    use HasFactory;
     
    protected $fillable = [
        'auth_user_id', 'name', 'slug', 'location', 'description', 'latitude', 'longitude'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
