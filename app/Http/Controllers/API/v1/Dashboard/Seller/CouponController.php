<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Requests\CategoryCreateRequest;
use App\Http\Resources\CouponResource;
use App\Http\Resources\ShopResource;
use App\Models\Coupon;
use App\Repositories\CouponRepository\CouponRepository;
use App\Services\CouponService\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class CouponController extends SellerBaseController
{
    private CouponRepository $couponRepository;
    private CouponService $couponService;

    /**
     * @param CouponRepository $couponRepository
     * @param CouponService $couponService
     */
    public function __construct(CouponRepository $couponRepository, CouponService $couponService)
    {
        parent::__construct();
        $this->couponRepository = $couponRepository;
        $this->couponService = $couponService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $coupons = $this->couponRepository->couponsList($request->all());
        return CouponResource::collection($coupons);
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        $coupons = $this->couponRepository->couponsPaginate($request->perPage, $this->shop->id);
        return CouponResource::collection($coupons);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        if ($this->shop && $this->shop->id == $request->shop_id){
            $result = $this->couponService->create($request);
            if ($result['status']) {
                return $this->successResponse(__('web.record_successfully_created'), CouponResource::make($result['data']));
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
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function show(int $id)
    {
        $coupon = $this->couponRepository->couponById($id, $this->shop->id);
        if ($coupon){
            $coupon->load('translations');
            return $this->successResponse(__('web.coupon_found'), CouponResource::make($coupon));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @param CategoryCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(int $id, Request $request)
    {
        if ($this->shop && $this->shop->id == $request->shop_id){
            $result = $this->couponService->update($id, $request);
            if ($result['status']) {
                return $this->successResponse(__('web.record_successfully_updated'), CouponResource::make($result['data']));
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
     * @return JsonResponse
     */
    public function destroy(int $id)
    {
        if ($this->shop){
            $coupon = Coupon::where(['id' => $id, 'shop_id' => $this->shop->id]);
            if ($coupon) {
                $coupon->delete();
                return $this->successResponse(__('web.record_successfully_deleted'), []);
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_NOT_FOUND
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
