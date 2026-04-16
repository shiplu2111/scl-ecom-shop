<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['product_id', 'sku', 'short_description', 'size', 'color', 'image', 'price', 'buying_price', 'discount_price'])]
class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryHistories()
    {
        return $this->hasMany(InventoryHistory::class, 'variant_id');
    }

    public function flashSaleItems()
    {
        return $this->hasMany(FlashSaleItem::class, 'variant_id');
    }

    public function activeFlashSaleItem()
    {
        return $this->hasOne(FlashSaleItem::class, 'variant_id')
            ->whereHas('flashSale', function ($query) {
                $query->active();
            });
    }

    /**
     * Get the final selling price for the variant, considering Flash Sales and Discounts.
     */
    public function getCalculatedPrice()
    {
        // 1. Priority: Active Flash Sale
        $flashSaleItem = $this->activeFlashSaleItem;
        if ($flashSaleItem && ($flashSaleItem->quantity_limit === null || $flashSaleItem->sold_quantity < $flashSaleItem->quantity_limit)) {
            return (float) $flashSaleItem->sale_price;
        }

        // 2. Priority: Discount Price
        if (!is_null($this->discount_price) && $this->discount_price > 0) {
            return (float) $this->discount_price;
        }

        // 3. Fallback: Regular Price
        return (float) ($this->price ?? 0);
    }
}
