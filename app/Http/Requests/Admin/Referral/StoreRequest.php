<?php

namespace App\Http\Requests\Admin\Referral;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
    public function rules()
    {
        return [
            'price_from'    => 'integer|max:21000000',
            'price_to'      => 'integer|max:21000000',
            'expired_at'    => 'date_format:Y-m-d',
            'title'         => 'array',
            'title.*'       => 'string|min:1|max:255',
            'description'   => 'array',
            'description.*' => 'string|min:1',
            'faq'           => 'array',
            'faq.*'         => 'string|min:1',
            'img'           => 'string',
        ];
    }
}
