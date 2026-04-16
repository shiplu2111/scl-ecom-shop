<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

#[Fillable(['user_id', 'order_number', 'subtotal', 'discount_amount', 'delivery_charge', 'grand_total', 'payment_method', 'payment_status', 'order_status', 'shipping_address_id', 'billing_address_id', 'shipping_full_name', 'shipping_phone', 'shipping_email', 'shipping_address_line', 'shipping_postal_code', 'shipping_division_id', 'shipping_district_id', 'shipping_thana_id', 'coupon_id', 'delivery_charge_paid', 'paid_amount', 'due_amount', 'courier_name', 'tracking_number', 'consignment_id', 'is_stock_reduced'])]
class Order extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(UserAddress::class, 'shipping_address_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function histories()
    {
        return $this->hasMany(OrderHistory::class);
    }
}
