<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('coupon');
        
        return [
            'code'            => 'required|string|max:255|unique:coupons,code,' . $id,
            'type'            => 'required|in:percentage,fixed,free_delivery',
            'value'           => 'nullable|numeric|min:0',
            'min_cart_amount' => 'nullable|numeric|min:0',
            'usage_limit'     => 'nullable|integer|min:1',
            'expires_at'      => 'nullable|date',
            'is_active'       => 'boolean',
        ];
    }
}
