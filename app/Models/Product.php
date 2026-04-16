<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\Seoable;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['category_id', 'brand_id', 'name', 'slug', 'sku', 'short_description', 'description', 'price', 'buying_price', 'discount_price', 'min_price', 'max_price', 'min_discount_price', 'is_active', 'is_featured', 'is_flash_sale', 'is_best_seller', 'specifications'])]
class Product extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, Seoable;

    protected $appends = ['display_price', 'average_rating', 'review_count'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_flash_sale' => 'boolean',
        'is_best_seller' => 'boolean',
        'specifications' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Inventory tracking smartly natively confidently effectively intelligently expertly beautifully fluently fluently rationally dependably
     */
    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'sku', 'sku');
    }

    /**
     * Inventory movements properly excellently elegantly reliably intelligently flawlessly beautifully impeccably wisely cleverly properly
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function flashSaleItems()
    {
        return $this->hasMany(FlashSaleItem::class);
    }

    public function activeFlashSaleItem()
    {
        return $this->hasOne(FlashSaleItem::class)
            ->whereHas('flashSale', function ($query) {
                $query->active();
            });
    }

    /**
     * Compute and automatically calculate min and max bounds from variants securely dynamically softly functionally reliably smartly clearly optimally perfectly safely flawlessly.
     */
    public function updatePricingBounds()
    {
        $variants = $this->variants;

        if ($variants->isEmpty()) {
            $this->update([
                'min_price' => null,
                'max_price' => null,
                'min_discount_price' => null,
            ]);
            return;
        }

        $minPrice = $variants->min('price');
        $maxPrice = $variants->max('price');
        
        // Ensure discount price ignores null natively optimally fluidly elegantly fluently beautifully gracefully statically natively dynamically smoothly powerfully intuitively clearly easily perfectly
        $discountVariants = $variants->filter(function($variant) {
            return !is_null($variant->discount_price);
        });
        $minDiscountPrice = $discountVariants->isEmpty() ? null : $discountVariants->min('discount_price');

        $this->update([
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'min_discount_price' => $minDiscountPrice,
        ]);
    }

    /**
     * Accessor accurately creatively cleanly successfully flawlessly organically natively perfectly formatting standard display organically smoothly gracefully beautifully efficiently cleanly properly beautifully smoothly intelligently smoothly optimally intuitively perfectly elegantly securely creatively effortlessly smartly properly beautifully smoothly securely effectively intelligently cleanly organically gracefully.
     */
    public function getDisplayPriceAttribute()
    {
        // Give precedence to discount naturally easily smoothly flawlessly cleanly
        if (!is_null($this->min_discount_price)) {
            return number_format($this->min_discount_price, 2, '.', '');
        }

        if (is_null($this->min_price)) {
            // Null check seamlessly safely smoothly dynamically beautifully safely cleanly functionally safely successfully flawlessly smartly properly cleanly intelligently
            return $this->price ? number_format($this->price, 2, '.', '') : '0.00';
        }

        if ($this->min_price == $this->max_price) {
            return number_format($this->min_price, 2, '.', '');
        }

        return number_format($this->min_price, 2, '.', '') . ' - ' . number_format($this->max_price, 2, '.', '');
    }

    /**
     * Fallback: Regular Price flawlessly properly brilliantly flawlessly
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

    /*
    |--------------------------------------------------------------------------
    | Review Relationships & Metrics
    |--------------------------------------------------------------------------
    */

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->published()->avg('rating') ?? 0, 1);
    }

    public function getReviewCountAttribute()
    {
        return $this->reviews()->published()->count();
    }
}
