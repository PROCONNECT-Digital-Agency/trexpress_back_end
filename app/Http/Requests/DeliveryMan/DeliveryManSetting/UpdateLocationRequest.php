<?php

namespace App\Http\Requests\DeliveryMan\DeliveryManSetting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'location'              => 'required|array',
            'location.latitude'     => 'required|numeric',
            'location.longitude'    => 'required|numeric',
        ];
    }
}
