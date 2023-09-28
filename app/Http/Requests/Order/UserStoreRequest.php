<?php

namespace App\Http\Requests\Order;

use App\Helpers\ResponseError;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class UserStoreRequest extends FormRequest
{
    use ApiResponse;
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
            'name' => 'required|string|min:3|max:200|regex:[a-zA-Z\s]+',
            'surname' => 'required|string|min:3|max:255|regex:[a-zA-Z\s]+',
            'birth_date' => 'nullable|date_format:Y-m-d|regex:/^[0-9]+$/',
            'gender' => 'required|string|in:male,female',
            'address' => 'required|string|min:10|max:200|regex:/^[A-Za-z0-9\-\s]+$/',
            'email' => 'required|string|email|min:10|max:250',
            'passport_number' => 'required|string|min:6|max:15|regex:/^[A-Za-z0-9\-\s]+$/',
            'passport_secret' => 'required|string|min:6|max:15|regex:/^[A-Za-z0-9\-\s]+$/',
            'number' => 'required|integer|min:11|max:13',
        ];
    }

    public function messages()
    {
        return [
            'required' => trans('validation.required', [], request()->lang),
            'integer' => trans('validation.integer', [], request()->lang),
            'min' => trans('validation.min', [], request()->lang),
            'max' => trans('validation.max', [], request()->lang),
            'regex' => trans('validation.regex', [], request()->lang),
            'in' => trans('validation.in', [], request()->lang),
            'email' => trans('validation.email', [], request()->lang),
            'string' => trans('validation.string', [], request()->lang),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = $this->requestErrorResponse(
            ResponseError::ERROR_400,
            trans('errors.' . ResponseError::ERROR_400, [], request()->lang),
            $errors->messages(), Response::HTTP_BAD_REQUEST);

        throw new HttpResponseException($response);
    }
}
