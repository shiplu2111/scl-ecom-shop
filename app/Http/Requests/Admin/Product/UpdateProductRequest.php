<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        $id = $this->route('product');
        return [
            'category_id'      => 'sometimes|required|exists:categories,id',
            'brand_id'         => 'nullable|exists:brands,id',
            'name'             => 'sometimes|required|string|max:255',
            'sku'              => ['sometimes', 'required', 'string', 'unique:products,sku,' . $id, 'max:100', 'regex:/^SCL-[A-Za-z0-9_-]+$/'],
            'short_description' => 'nullable|string|max:1000',
            'description'      => 'nullable|string',
            'price'            => 'sometimes|required|numeric|min:0',
            'discount_price'   => 'nullable|numeric|min:0',
            'buying_price'     => 'nullable|numeric|min:0',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'is_active'        => 'boolean',
            'is_featured'      => 'boolean',
            'is_flash_sale'    => 'boolean',
            'is_best_seller'   => 'boolean',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'images'           => 'nullable|array',
            'images.*'         => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'variants'         => 'nullable|array',
            'variants.*.id'    => 'nullable|exists:product_variants,id',
            'variants.*.sku'   => 'required|string',
            'variants.*.short_description' => 'nullable|string|max:1000',
            'variants.*.size'  => 'nullable|string',
            'variants.*.color' => 'nullable|string',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.discount_price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.buying_price' => 'nullable|numeric|min:0',
            'variants.*.supplier_id' => 'nullable|exists:suppliers,id',
            'stock'            => 'nullable|integer|min:0',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'specifications'   => 'nullable|array',
            'specifications.*.key' => 'required_with:specifications|string',
            'specifications.*.value' => 'required_with:specifications|string',
        ];
    }
}
