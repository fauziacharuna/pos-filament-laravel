<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'stock',
        'description',
        'image',
        'category',
        'supplier',
        'sku',
        'is_active',
    ];
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
    //
}
