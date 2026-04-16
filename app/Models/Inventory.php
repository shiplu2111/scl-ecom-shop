<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'buying_price',
        'supplier_id',
        'quantity',
        'alert_quantity'
    ];

    public static function forSku(?string $sku): int
    {
        if (!$sku) return 0;
        return self::where('sku', $sku)->value('quantity') ?? 0;
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'sku', 'sku');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'sku', 'sku');
    }
}
