<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'category_id'      => 'required|exists:categories,id',
            'brand_id'         => 'nullable|exists:brands,id',
            'name'             => 'required|string|max:255',
            'sku'              => 'required|string|unique:products,sku|max:100',
            'description'      => 'nullable|string',
            'price'            => 'required|numeric|min:0',
            'is_active'        => 'boolean',
            'variants'         => 'nullable|array',
            'variants.*.sku'   => 'required|string|unique:product_variants,sku',
            'variants.*.size'  => 'nullable|string',
            'variants.*.color' => 'nullable|string',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
        ];
    }
}
