<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAddressRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'full_name'    => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'label'        => 'nullable|string|max:50',
            'division_id'  => 'required|exists:divisions,id',
            'district_id'  => 'required|exists:districts,id',
            'thana_id'     => 'required|exists:thanas,id',
            'address_line' => 'required|string|max:255',
            'postal_code'  => 'required|string|max:20',
            'is_default'   => 'boolean'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $thanaId = $this->input('thana_id');
            $postalCode = $this->input('postal_code');

            if ($thanaId && $postalCode) {
                $thana = \App\Models\Thana::find($thanaId);
                if ($thana && !empty($thana->postal_code) && $thana->postal_code !== $postalCode) {
                    $validator->errors()->add('postal_code', 'The postal code does not match the selected Thana.');
                }
            }
        });
    }
}
