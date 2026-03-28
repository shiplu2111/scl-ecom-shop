<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['product_id', 'sku', 'size', 'color', 'price', 'buying_price', 'discount_price', 'stock'])]
class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = ['buying_price'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryHistories()
    {
        return $this->hasMany(InventoryHistory::class, 'variant_id');
    }
}
