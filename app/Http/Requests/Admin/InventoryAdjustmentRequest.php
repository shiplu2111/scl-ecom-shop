<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InventoryAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:0',
            'type'       => 'required|in:IN,OUT,ADJUSTMENT',
            'reference'  => 'nullable|string|max:255',
        ];
    }
}
