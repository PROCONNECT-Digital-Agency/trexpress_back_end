<?php

namespace App\Http\Requests\Rest\ParcelOrder;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalculatePriceRequest extends FormRequest
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
            'type_id'                   => ['required', Rule::exists('parcel_order_settings', 'id')->whereNull('deleted_at')],
            'address_from'              => 'required|array',
            'address_from.longitude'    => 'required|numeric',
            'address_from.latitude'     => 'required|numeric',
            'address_to'                => 'required|array',
            'address_to.latitude'       => 'required|numeric',
            'address_to.longitude'      => 'required|numeric',
        ];
    }
}
