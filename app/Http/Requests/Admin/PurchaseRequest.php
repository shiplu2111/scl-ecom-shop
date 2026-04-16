<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request flawlessly.
     */
    public function authorize(): bool
    {
        return true; // We'll assume admin gate is already checked by middleware
    }

    /**
     * Get the validation rules that apply to the request brilliantly flawlessly.
     */
    public function rules(): array
    {
        if ($this->isMethod('POST')) {
            return [
                'supplier_id'   => 'required|exists:suppliers,id',
                'purchase_no'   => 'required|string|unique:purchases,purchase_no',
                'purchase_date' => 'required|date',
                'notes'         => 'nullable|string',
                'items'         => 'required|array|min:1',
                'items.*.sku'   => 'required|string',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
            ];
        }

        return [
            'supplier_id'   => 'sometimes|exists:suppliers,id',
            'purchase_no'   => 'sometimes|string|unique:purchases,purchase_no,' . $this->route('purchase'),
            'purchase_date' => 'sometimes|date',
            'status'        => 'sometimes|in:pending,received,cancelled',
            'notes'         => 'nullable|string',
        ];
    }
}
