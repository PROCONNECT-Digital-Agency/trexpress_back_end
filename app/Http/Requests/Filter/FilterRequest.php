<?php

namespace App\Http\Requests\Filter;

use App\Helpers\ResponseError;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class FilterRequest extends FormRequest
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
            'parent_category_id' => 'nullable|integer',
            'categoryIds' => 'array',
            'categoryIds.*.' => 'integer|exists:categories,id',
            'brandIds' => 'array',
            'brandIds.*.' => 'integer|exists:brands,id',
            'extrasIds' => 'array',
            'extrasIds.*.' => 'integer|exists:extra_values,id',
            'shopIds' => 'array',
            'shopIds.*.' => 'integer|exists:shops,id',
            'range' => 'array',
            'range.*.' => 'integer|numeric',
            'perPage' => 'nullable|integer',
            'price_asc' => 'nullable',
            'price_desc' => 'nullable',
            'sortByAsc' => 'nullable',
            'sortByDesc' => 'nullable',
            'column_price' => 'nullable',
            'sort' => 'nullable',
            'category_id' => 'nullable|integer'
        ];
    }

    public function messages(): array
    {
        return [
            'array' => trans('validation.array', [], request()->lang),
            'exists' => trans('validation.exists', [], request()->lang),
            'integer' => trans('validation.integer', [], request()->lang),
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
