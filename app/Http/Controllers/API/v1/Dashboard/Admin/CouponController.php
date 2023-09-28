<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\Admin\Coupon\StoreRequest;
use App\Http\Requests\Admin\Coupon\UpdateRequest;
use App\Http\Requests\CategoryCreateRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use App\Repositories\CouponRepository\CouponRepository;
use App\Services\CouponService\CouponService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class CouponController extends AdminBaseController
{
    use ApiResponse;

    /**
     * @param CouponRepository $couponRepository
     * @param CouponService $couponService
     */
    public function __construct(private CouponRepository $couponRepository,private CouponService $couponService)
    {
        parent::__construct();
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
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request): AnonymousResourceCollection
    {
        $coupons = $this->couponRepository->couponsPaginate($request->perPage);
        return CouponResource::collection($coupons);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $result = $this->couponService->create($collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), CouponResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function show(int $id): JsonResponse|AnonymousResourceCollection
    {
        $coupon = $this->couponRepository->couponById($id);
        if ($coupon) {
            $coupon->load('translations');
            return $this->successResponse(__('web.coupon_found'), CouponResource::make($coupon));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(int $id, UpdateRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->couponService->update($id, $collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'), CouponResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $coupon = Coupon::where(['id' => $id])->first();
        if ($coupon) {
            $coupon->delete();
            return $this->successResponse(__('web.record_successfully_deleted'), []);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language ?? config('app.locale')),
            Response::HTTP_NOT_FOUND
        );
    }
}
