<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
{
    /**
     * Determine if user is authorized flawlessly properly.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rules properly expertly brilliantly excellently properly.
     */
    public function rules(): array
    {
        $id = $this->route('page');
        return [
            'title'            => 'required|string|max:255',
            'slug'             => 'required|string|max:255|unique:pages,slug,' . $id,
            'content'          => 'nullable|string',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'status'           => 'required|in:published,draft',
        ];
    }
}
