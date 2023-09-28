<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\DashboardRepository\DashboardRepository;
use Illuminate\Http\Request;

class DashboardController extends AdminBaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function countStatistics(Request $request)
    {
        $result = (new DashboardRepository())->statisticCount($request->all());
        return $this->successResponse(__('web.statistics_count'), $result);

    }

    public function sumStatistics(Request $request)
    {
        $result = (new DashboardRepository())->statisticSum($request->all());
        return $this->successResponse(__('web.statistics_sum'), $result);
    }

    public function topCustomersStatistics(Request $request)
    {
        $result = (new DashboardRepository())->statisticTopCustomer($request->perPage ?? 15, $request->all());
        return UserResource::collection($result);
    }

    public function topProductsStatistics(Request $request)
    {
        $result = (new DashboardRepository())->statisticTopSoldProducts($request->perPage ?? 15, $request->all());
        return ProductResource::collection($result);
    }

    public function ordersSalesStatistics(Request $request)
    {
        $result = (new DashboardRepository())->statisticOrdersSales($request->all());
        return $this->successResponse(__('web.statistics_orders_sales'), $result);
    }

    public function ordersCountStatistics(Request $request)
    {
        $result = (new DashboardRepository())->statisticOrdersCount($request->all());
        return $this->successResponse(__('web.statistics_order_count'), $result);
    }
}
