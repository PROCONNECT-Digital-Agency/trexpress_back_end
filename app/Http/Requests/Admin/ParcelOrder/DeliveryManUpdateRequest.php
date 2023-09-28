<?php

namespace App\Http\Requests\Admin\ParcelOrder;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryManUpdateRequest extends FormRequest
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
            'deliveryman_id' => [
                'int',
                'required',
                Rule::exists('users', 'id')->whereNull('deleted_at')
            ],
        ];
    }
}
