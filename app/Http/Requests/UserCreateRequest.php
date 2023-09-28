<?php

namespace App\Http\Requests;

use App\Helpers\ResponseError;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UserCreateRequest extends FormRequest
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
            'firstname' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['email', 'unique:users'],
            'phone' => ['numeric', 'unique:users'],
            'gender' => ['string', Rule::in('male','female')],
            'active' => ['numeric', Rule::in(1,0)],
            'password' => ['min:6', 'confirmed']
        ];
    }

    public function messages(): array
    {
        return [
            'required' => trans('validation.required', [], request()->lang),
            'numeric' => trans('validation.numeric', [], request()->lang),
            'min' => trans('validation.min.numeric', [], request()->lang),
            'max' => trans('validation.max', [], request()->lang),
            'array' => trans('validation.array', [], request()->lang),
            'string' => trans('validation.string', [], request()->lang),
            'unique' => trans('validation.unique', [], request()->lang),
            'in' => trans('validation.in', [], request()->lang),
            'email' => trans('validation.email', [], request()->lang),
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
