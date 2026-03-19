<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'shipping_address_id' => 'required|exists:user_addresses,id',
            'billing_address_id' => 'nullable|exists:user_addresses,id',
            'payment_method' => 'required|in:cod,online'
        ];
    }
}
