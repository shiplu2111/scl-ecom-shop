<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'type', 'value', 'min_cart_amount', 'usage_limit', 'used_count', 'expires_at', 'is_active'])]
class Coupon extends Model
{
    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'value' => 'decimal:2',
        'min_cart_amount' => 'decimal:2',
    ];

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
