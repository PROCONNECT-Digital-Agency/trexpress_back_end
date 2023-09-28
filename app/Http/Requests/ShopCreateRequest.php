<?php

namespace App\Http\Requests;

use App\Helpers\ResponseError;
use App\Models\Shop;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ShopCreateRequest extends FormRequest
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
            'open_time' => ['required', 'string'],
            'close_time' => ['required', 'string'],
            'status' => ['string', Rule::in(Shop::STATUS)],
            'active' => ['numeric', Rule::in(1,0)],

            "title"    => ['required', 'array'],
            "title.*"  => ['required', 'string', 'max:199'],
            "description"  => ['array'],
            "description.*"  => ['string'],
            "address"    => ['required', 'array'],
            "address.*"  => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => trans('validation.required', [], request()->lang),
            'regex' => trans('validation.regex', [], request()->lang),
            'numeric' => trans('validation.numeric', [], request()->lang),
            'min' => trans('validation.min.numeric', [], request()->lang),
            'array' => trans('validation.array', [], request()->lang),
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
