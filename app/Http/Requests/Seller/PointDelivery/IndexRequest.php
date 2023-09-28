<?php

namespace App\Http\Requests\Seller\PointDelivery;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
     * @return array<string, array{perPage: string}>
     */
    public function rules(): array
    {
        return [
            'perPage' => 'required|integer'
        ];
    }
}
