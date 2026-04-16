<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Flexible Shipping (Saved ID or manual details)
            'shipping_address_id' => 'nullable|exists:user_addresses,id',
            'billing_address_id'  => 'nullable|exists:user_addresses,id',
            
            // Re-enabled manual fields for order snapshotting flawlessly brilliantly properly
            'full_name'    => 'required|string|max:100',
            'phone'        => 'required|string|max:20',
            'email'        => 'nullable|email|max:100',
            'address'      => 'required|string|max:255',
            'postal_code'  => 'nullable|string|max:10',
            'division_id'  => 'required|exists:divisions,id',
            'district_id'  => 'required|exists:districts,id',
            'thana_id'     => 'required|exists:thanas,id',

            'payment_method' => 'required|in:cod,online',
            'gateway'        => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'division_id.exists' => 'Please select a valid division.',
            'district_id.exists' => 'Please select a valid district.',
            'thana_id.exists'    => 'Please select a valid area/thana.',
            'payment_method.in'  => 'Invalid payment method selected.',
        ];
    }

    /**
     * Return JSON on validation failure (not a redirect).
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
