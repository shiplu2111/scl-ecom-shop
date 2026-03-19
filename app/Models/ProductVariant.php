<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['product_id', 'sku', 'size', 'color', 'price', 'stock'])]
class ProductVariant extends Model
{
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryHistories()
    {
        return $this->hasMany(InventoryHistory::class, 'variant_id');
    }
}
