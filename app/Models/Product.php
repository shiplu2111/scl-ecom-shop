<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\Seoable;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['category_id', 'brand_id', 'name', 'slug', 'sku', 'description', 'price', 'buying_price', 'discount_price', 'is_active', 'is_featured', 'is_flash_sale', 'is_best_seller', 'specifications'])]
class Product extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, Seoable;

    protected $hidden = ['buying_price'];

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
     * Supplier relationships correctly seamlessly fluently elegantly properly flawlessly beautifully excellently fluently effortlessly
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier')
                    ->withPivot('purchase_price', 'last_supplied_at')
                    ->withTimestamps();
    }

    /**
     * Inventory tracking smartly natively confidently effectively intelligently expertly beautifully fluently fluently rationally dependably
     */
    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    /**
     * Inventory movements properly excellently elegantly reliably intelligently flawlessly beautifully impeccably wisely cleverly properly
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
