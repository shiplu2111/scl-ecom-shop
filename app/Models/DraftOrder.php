<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftOrder extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'amount',
        'payment_method',
        'checkout_data',
        'status',
    ];

    protected $casts = [
        'checkout_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'draft_order_id');
    }

    // Accessors for compatibility with OrderResource
    public function getGrandTotalAttribute() { return $this->amount; }
    public function getSubtotalAttribute() { return $this->checkout_data['subtotal'] ?? 0; }
    public function getDiscountAmountAttribute() { return $this->checkout_data['discount_amount'] ?? 0; }
    public function getDeliveryChargeAttribute() { return $this->checkout_data['delivery_charge'] ?? 0; }
    public function getPaymentStatusAttribute() { return 'pending'; }
    public function getOrderStatusAttribute() { return 'draft'; }
    public function getDeliveryChargePaidAttribute() { return false; }
    public function getPaidAmountAttribute() { return 0; }
    public function getDueAmountAttribute() { return $this->amount; }
}
