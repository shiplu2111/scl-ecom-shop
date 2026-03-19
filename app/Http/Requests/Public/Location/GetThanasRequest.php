<?php

namespace App\Http\Requests\Public\Location;

use Illuminate\Foundation\Http\FormRequest;

class GetThanasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'district_id' => 'sometimes|nullable|exists:districts,id',
        ];
    }
}
