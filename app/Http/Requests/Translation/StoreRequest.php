<?php

namespace App\Http\Requests\Translation;

use App\Helpers\ResponseError;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreRequest extends FormRequest
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
            'key' => 'required|alpha_dash'
        ];
    }

    public function messages()
    {
        return [
            'required' => trans('validation.required', [], request()->lang),
            'alpha_dash' => trans('validation.alpha_dash', [], request()->lang),
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
