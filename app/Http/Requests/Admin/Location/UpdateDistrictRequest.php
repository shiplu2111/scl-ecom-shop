<?php

namespace App\Http\Requests\Admin\Location;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDistrictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'division_id' => ['sometimes', 'exists:divisions,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'delivery_charge' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
