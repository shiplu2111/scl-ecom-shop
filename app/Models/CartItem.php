<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[Fillable(['cart_id', 'product_id', 'product_variant_id', 'quantity'])]
class CartItem extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function getResolvedPrice()
    {
        // 1. Check for Active Flash Sale (Highest priority)
        $flashSaleItem = \App\Models\FlashSaleItem::where('product_id', $this->product_id)
            ->where(function($q) {
                $q->whereNull('variant_id')->orWhere('variant_id', $this->product_variant_id);
            })
            ->whereHas('flashSale', function ($q) {
                $q->active();
            })
            ->orderByRaw('variant_id IS NULL ASC') 
            ->first();

        if ($flashSaleItem && ($flashSaleItem->quantity_limit === null || $flashSaleItem->sold_quantity < $flashSaleItem->quantity_limit)) {
            return $flashSaleItem->sale_price;
        }

        // 2. Check for Variant Discount Price
        if ($this->productVariant) {
            if ($this->productVariant->discount_price > 0) {
                return $this->productVariant->discount_price;
            }
            return $this->productVariant->price;
        }

        // 3. Check for Product Discount Price
        if ($this->product && $this->product->discount_price > 0) {
           return $this->product->discount_price;
        }

        return $this->product ? $this->product->price : 0;
    }
}
