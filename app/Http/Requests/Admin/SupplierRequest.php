<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone'        => 'required|string|max:20',
            'email'        => 'nullable|email|max:255',
            'address'      => 'required|string',
            'status'       => 'nullable|in:active,inactive',
            'products'     => 'nullable|array',
            'products.*.product_id'     => 'required|exists:products,id',
            'products.*.purchase_price' => 'required|numeric|min:0',
        ];
    }
}
