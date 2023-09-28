<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;
use App\Repositories\DashboardRepository\DashboardRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends SellerBaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function countStatistics(Request $request)
    {
        if ($this->shop) {
            $result = (new DashboardRepository())->sellerStatisticCount($request->merge(['shop_id' => $this->shop->id])->all());
            return $this->successResponse(__('web.statistics_count'), $result);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function sumStatistics(Request $request)
    {
        if ($this->shop) {
            $result = (new DashboardRepository())->statisticSum($request->merge(['shop_id' => $this->shop->id])->all());
            return $this->successResponse(__('web.statistics_sum'), $result);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function topCustomersStatistics(Request $request)
    {
        if ($this->shop) {
            $result = (new DashboardRepository())->statisticTopCustomer($request->perPage ?? 15, $request->merge(['shop_id' => $this->shop->id])->all());
            return UserResource::collection($result);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function topProductsStatistics(Request $request)
    {
        if ($this->shop) {
            $result = (new DashboardRepository())->statisticTopSoldProducts($request->perPage ?? 15, $request->merge(['shop_id' => $this->shop->id])->all());
            return ProductResource::collection($result);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function ordersSalesStatistics(Request $request)
    {
        if ($this->shop) {
            $result = (new DashboardRepository())->statisticOrdersSales($request->merge(['shop_id' => $this->shop->id])->all());
            return $this->successResponse(__('web.statistics_orders_sales'), $result);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function ordersCountStatistics(Request $request)
    {
        if ($this->shop) {
            $result = (new DashboardRepository())->statisticOrdersCount($request->merge(['shop_id' => $this->shop->id])->all());
            return $this->successResponse(__('web.statistics_order_count'), $result);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }
}
