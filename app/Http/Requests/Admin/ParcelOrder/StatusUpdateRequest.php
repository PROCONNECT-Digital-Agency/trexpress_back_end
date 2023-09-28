<?php

namespace App\Http\Requests\Admin\ParcelOrder;

use App\Models\ParcelOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StatusUpdateRequest extends FormRequest
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
            'status' => [
                'string',
                'required',
                Rule::in(ParcelOrder::STATUSES)
            ],
        ];
    }
}
