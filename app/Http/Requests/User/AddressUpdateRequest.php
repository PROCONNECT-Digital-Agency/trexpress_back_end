<?php

namespace App\Http\Requests\User;

use App\Helpers\ResponseError;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class AddressUpdateRequest extends FormRequest
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
            'title' => 'required|string|max:191',
            'location' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'apartment' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
        ];
    }
}
