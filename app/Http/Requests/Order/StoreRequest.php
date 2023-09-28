<?php

namespace App\Http\Requests\Order;

use App\Helpers\ResponseError;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id',
            'total' => 'required|numeric',
            'total_delivery_fee' => 'required|numeric',
            'tax' => 'required|numeric',
            'rate' => 'required|numeric',
            'user_address_id' => 'required|integer|exists:user_addresses,id',
            'delivery_id' => 'required|integer|exists:deliveries,id',
            'currency_id' => 'required|integer|exists:currencies,id',
            'note' => 'nullable|string',
            'shops' => 'array',
            'shops.*.shop_id' => 'required|integer|exists:shops,id',
            'shops.*.tax' => 'required|numeric',
            'shops.*.commission_fee' => 'required|numeric',
            'shops.*.status' => 'required|string',
            'shops.*.delivery_type_id' => 'required|integer|exists:deliveries,id',
            'shops.*.delivery_fee' => 'required|numeric',
            'shops.*.delivery_address_id' => 'nullable|integer',
            'shops.*.delivery_date' => 'nullable',
            'shops.*.delivery_time' => 'nullable|integer',
            'shops.*.products' => 'required|array',
        ];
    }

    public function messages()
    {
        return [
            'required' => trans('validation.required', [], request()->lang),
            'integer' => trans('validation.integer', [], request()->lang),
            'exists' => trans('validation.exists', [], request()->lang),
            'numeric' => trans('validation.numeric', [], request()->lang),
            'string' => trans('validation.string', [], request()->lang),
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
