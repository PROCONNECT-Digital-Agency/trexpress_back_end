<?php

namespace App\Http\Requests\Admin\DeliveryManSetting;

use App\Models\DeliveryManSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'user_id' => [
                'required',
                'integer',
                Rule::unique('delivery_man_settings', 'user_id')
                    ->ignore(data_get(DeliveryManSetting::find(request()->route('deliveryman_setting')), 'user_id'), 'user_id'),
                Rule::exists('users', 'id')
                    ->whereNull('deleted_at')
            ],
            'type_of_technique'     => ['string', Rule::in(DeliveryManSetting::TYPE_OF_TECHNIQUES)],
            'brand'                 => 'string',
            'model'                 => 'string',
            'number'                => 'string',
            'color'                 => 'string',
            'order_quantity'        => 'required|integer',
            'online'                => 'boolean',
            'location'              => 'array',
            'location.latitude'     => is_array(request('location')) ? 'required|numeric' : 'numeric',
            'location.longitude'    => is_array(request('location')) ? 'required|numeric' : 'numeric',
            'images'                => 'array',
            'images.*'              => 'string',
        ];
    }
}
