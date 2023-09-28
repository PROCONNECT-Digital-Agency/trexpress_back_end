<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Resources\DiscountResource;
use App\Models\Discount;
use App\Repositories\DiscountRepository\DiscountRepository;
use App\Services\DiscountService\DiscountService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DiscountController extends SellerBaseController
{
    private DiscountRepository $discountRepository;
    private DiscountService $discountService;

    /**
     * @param DiscountRepository $discountRepository
     * @param DiscountService $discountService
     */
    public function __construct(DiscountRepository $discountRepository, DiscountService $discountService)
    {
        parent::__construct();
        $this->discountRepository = $discountRepository;
        $this->discountService = $discountService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function paginate(Request $request)
    {
        if ($this->shop){
            $discounts = $this->discountRepository->discountsPaginate(
                $request->perPage ?? 15, $this->shop->id, $request->active ?? null, $request->all()
            );
            return DiscountResource::collection($discounts);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($this->shop){
            $result = $this->discountService->create($request->merge(['shop_id' => $this->shop->id]));
            if ($result['status']) {
                return $this->successResponse(__('web.record_successfully_created'), DiscountResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function show(int $id)
    {
        if ($this->shop) {
            $discount = $this->discountRepository->discountDetails($id, $this->shop->id);
            if ($discount) {
                return $this->successResponse(__('web.discount_found'), DiscountResource::make($discount));
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang  ?? 'en'),
                Response::HTTP_NOT_FOUND
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang ?? 'en'),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        if ($this->shop){
            $result = $this->discountService->update($id, $request->merge(['shop_id' => $this->shop->id]));
            if ($result['status']) {
                return $this->successResponse(__('web.record_successfully_updated'), DiscountResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        if ($this->shop){
            $discount = Discount::firstWhere(['id' => $id, 'shop_id' => $this->shop->id]);
            if ($discount) {
                $discount->delete();
                return $this->successResponse(__('web.record_has_been_successfully_delete'));
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], \request()->lang ?? 'en'),
                Response::HTTP_NOT_FOUND
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang ?? 'en'),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function setActiveStatus($id)
    {
        if ($this->shop) {
            $discount = Discount::firstWhere(['id' => $id, 'shop_id' => $this->shop->id]);
            if ($discount) {
                $discount->update(['active' => !$discount->active]);
                return $this->successResponse(__('web.record_active_update'), DiscountResource::make($discount));
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], \request()->lang ?? 'en'),
                Response::HTTP_NOT_FOUND
            );
           } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang ?? 'en'),
                Response::HTTP_FORBIDDEN
            );
        }
    }
}
