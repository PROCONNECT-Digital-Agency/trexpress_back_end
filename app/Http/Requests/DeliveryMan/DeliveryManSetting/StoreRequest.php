<?php

namespace App\Http\Requests\DeliveryMan\DeliveryManSetting;

use App\Models\DeliveryManSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
            'type_of_technique'     => ['required', 'string', Rule::in(DeliveryManSetting::TYPE_OF_TECHNIQUES)],
            'brand'                 => 'required|string',
            'model'                 => 'required|string',
            'number'                => 'required|string',
            'color'                 => 'required|string',
            'online'                => 'required|boolean',
            'order_quantity'        => 'required|integer',
            'location'              => 'array',
            'location.latitude'     => 'numeric',
            'location.longitude'    => 'numeric',
            'images'                => 'array',
            'images.*'              => 'string',
        ];
    }
}
