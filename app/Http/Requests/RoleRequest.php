<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $roleId = $this->route('role');
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($roleId)->where(function ($query) {
                    return $query->where('guard_name', 'admin');
                }),
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name'
        ];
    }
}
