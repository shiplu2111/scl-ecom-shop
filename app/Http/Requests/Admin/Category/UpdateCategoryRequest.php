<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('category');
        return [
            'parent_id' => 'nullable|exists:categories,id',
            'name'      => 'sometimes|required|string|max:255',
            'slug'      => 'sometimes|required|string|max:255|unique:categories,slug,' . $id,
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ];
    }
}
