<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ];
    }
}
