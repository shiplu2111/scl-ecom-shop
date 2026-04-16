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
            'sku'       => 'required|exists:inventories,sku',
            'quantity'  => 'required|integer|min:0',
            'type'      => 'required|in:IN,OUT,ADJUSTMENT',
            'reference' => 'nullable|string|max:255',
        ];
    }
}
