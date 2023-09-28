<?php

namespace App\Http\Requests\Admin\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'name'        => 'required|string|max:255|unique:coupons,name,'.$this->coupon,
            'type'        => 'required|string|in:fix,percent',
            'qty'         => 'required|integer|max:1000000000',
            'price'       => 'required',
            'expired_at'  => 'required|date_format:Y-m-d',
            'title'         => 'required|array',
            'title.*'       => 'string|min:1|max:255',
            'description'   => 'array',
            'description.*' => 'string|min:1',
        ];
    }
}
