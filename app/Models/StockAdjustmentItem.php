<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_adjustment_id',
        'sku',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    public function adjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'sku', 'sku');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'sku', 'sku');
    }
}
