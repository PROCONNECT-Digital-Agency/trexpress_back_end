<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use App\Services\Interfaces\CurrencyServiceInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CurrencyController extends AdminBaseController
{
    private Currency $model;
    private CurrencyServiceInterface $currencyService;

    /**
     * @param Currency $model
     * @param CurrencyServiceInterface $currencyService
     */
    public function __construct(Currency $model, CurrencyServiceInterface $currencyService)
    {
        parent::__construct();
        $this->model = $model;
        $this->currencyService = $currencyService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $currencies = $this->model->currenciesList();
        return $this->successResponse(__('web.list_of_currencies'), CurrencyResource::collection($currencies));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $result = $this->currencyService->create($request);
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_create'), CurrencyResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        $currency = $this->model->find($id);
        if ($currency) {
            return $this->successResponse(__('web.currency_found'), CurrencyResource::make($currency));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $result = $this->currencyService->update($id, $request);
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_create'), CurrencyResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        $result = $this->currencyService->delete($id);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Get Currency where "default = 1".
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDefaultCurrency()
    {
        $currency = $this->model->whereDefault(1)->first();
        if ($currency) {
            return $this->successResponse(__('web.currency_found'), CurrencyResource::make($currency));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Get all Active languages
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveCurrencies()
    {
        $languages = $this->model->whereActive(1)->get();
        return $this->successResponse(__('web.list_of_active_currencies'), CurrencyResource::collection($languages));
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function setActive(int $id)
    {
        $currency = $this->model->find($id);
        if ($currency) {
            $currency->update(['active' => !$currency->active]);

            return $this->successResponse(__('web.record_has_been_successfully_updated'), CurrencyResource::make($currency));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
