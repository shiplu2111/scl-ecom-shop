<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code'            => 'required|unique:coupons,code|string|max:255',
            'type'            => 'required|in:percentage,fixed,free_delivery',
            'value'           => 'nullable|numeric|min:0',
            'min_cart_amount' => 'nullable|numeric|min:0',
            'usage_limit'     => 'nullable|integer|min:1',
            'expires_at'      => 'nullable|date',
            'is_active'       => 'boolean',
        ];
    }
}
