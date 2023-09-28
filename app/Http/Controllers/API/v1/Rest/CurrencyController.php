<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends RestBaseController
{
    private Currency $model;

    public function __construct(Currency $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        $currencies = $this->model->orderByDesc('default')->get();
        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR), CurrencyResource::collection($currencies));
    }

    /**
     * Get all Active languages
     * @return \Illuminate\Http\JsonResponse
     */
    public function active()
    {
        $currencies = $this->model->where('active',1)->get();
        return $this->successResponse(__('web.list_of_active_currencies'), CurrencyResource::collection($currencies));
    }
}
