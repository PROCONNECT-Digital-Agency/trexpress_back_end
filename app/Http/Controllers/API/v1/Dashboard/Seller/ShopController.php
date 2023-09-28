<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\ReportChartRequest;
use App\Http\Requests\ReportCompareRequest;
use App\Http\Requests\ReportPaginateRequest;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use App\Repositories\Interfaces\ShopRepoInterface;
use App\Services\Interfaces\ShopServiceInterface;
use App\Services\ShopServices\ShopActivityService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShopController extends SellerBaseController
{
    private ShopRepoInterface $shopRepository;
    private ShopServiceInterface $shopService;

    public function __construct(ShopRepoInterface $shopRepository, ShopServiceInterface $shopService)
    {
        parent::__construct();
        $this->shopRepository = $shopRepository;
        $this->shopService = $shopService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|
     */
    public function shopCreate(Request $request)
    {
        if (!$this->shop){
            $result = $this->shopService->create($request);
            if ($result['status']) {
                auth('sanctum')->user()->invitations()->delete();
                return $this->successResponse(__('web.record_successfully_created'), ShopResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_205, __('errors.' . ResponseError::ERROR_205, [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @return ShopResource|\Illuminate\Http\JsonResponse
     */
    public function shopShow()
    {
        if ($this->shop) {
            $shop = $this->shopRepository->show($this->shop->uuid);
            if ($shop){
                return $this->successResponse(__('errors.'.ResponseError::NO_ERROR), ShopResource::make($shop->load('translations', 'seller.wallet')));
            }
            return $this->errorResponse(
                ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], \request()->lang ?? 'en'),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function shopUpdate(Request $request)
    {
        if ($this->shop) {
            $request->merge(['user_id' => $this->shop->user_id]);
            $result = $this->shopService->update($this->shop->uuid, $request);
            if ($result['status']) {
                return $this->successResponse(__('web.record_successfully_updated'), ShopResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function setVisibilityStatus()
    {
        if ($this->shop) {
            (new ShopActivityService())->changeVisibility($this->shop->uuid);
            return $this->successResponse(__('web.record_successfully_updated'), ShopResource::make($this->shop));
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function setWorkingStatus()
    {
        if ($this->shop) {
            $shop = Shop::find($this->shop->id);
            $data = $this->shop->update(['open' => !$this->shop->open]);
            return $this->successResponse(__('web.record_successfully_updated'), ShopResource::make($shop));
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getWithSeller(FilterParamsRequest $filterParamsRequest)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->shopRepository->getShopWithSellerCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function reportPaginate(ReportPaginateRequest $request, FilterParamsRequest $filterParamsRequest)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->shopRepository->reportPaginateCache($filterParamsRequest->get('perPage', 15));

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function reportChart(ReportChartRequest $request)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->shopRepository->reportChartCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function reportCompare()
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->shopRepository->reportCompareCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

}
