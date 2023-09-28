<?php

namespace App\Http\Requests\Seller\PointDelivery;

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
    public function rules(): array
    {
        return [
            'location' => 'required|string',
            'keep_days' => 'required|numeric',
            'working_time' => 'required|string',
            'title' => 'required|array'
        ];
    }
}
