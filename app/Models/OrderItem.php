<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'order_id', 
    'product_id', 
    'product_variant_id', 
    'product_name', 
    'variant_name', 
    'sku', 
    'quantity', 
    'unit_price', 
    'buying_price', 
    'total_price'
])]
class OrderItem extends Model
{
    /**
     * The attributes that should be hidden for serialization.
     * Hidden by default, but exposed manually in resources when needed.
     */
    protected $hidden = [];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
