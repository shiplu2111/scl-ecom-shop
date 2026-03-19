<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $adminId = $this->route('admin'); // 'admin' parameter when updating

        $rules = [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:admins,email' . ($adminId ? ',' . $adminId : ''),
            'role'      => 'required|string|exists:roles,name',
            'is_active' => 'boolean'
        ];

        if ($this->isMethod('post')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }
}
