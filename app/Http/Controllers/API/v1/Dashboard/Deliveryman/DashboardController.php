<?php

namespace App\Http\Controllers\API\v1\Dashboard\Deliveryman;

use App\Helpers\ResponseError;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Repositories\DashboardRepository\DashboardRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends DeliverymanBaseController
{
    private Order $model;
    public function __construct(Order $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    public function countStatistics(Request $request)
    {
        // GET ORDERS COUNT
        $orders = $this->model
            ->with('orderDetails')
            ->where('deliveryman_id', auth('sanctum')->id())
            ->count();

        // GET ORDERS WITH CANCELED STATUS
        $delivered = $this->model->with('orderDetails')
            ->where('status',Order::DELIVERED)
            ->where('deliveryman_id', auth('sanctum')->id())
            ->count();

        // GET ORDERS WITH CANCELED STATUS
        $canceled = $this->model->with('orderDetails')
            ->where('status',Order::CANCELED)
            ->where('deliveryman_id', auth('sanctum')->id())
            ->count();

        // GET ORDERS WITH PROGRESS STATUS
        $progress = $this->model->with('orderDetails')
            ->whereNotIn('status', [Order::DELIVERED,Order::CANCELED])
            ->count();


        return $this->successResponse(__('web.statistics_count'),  [
            'progress_orders_count' => $progress,
            'delivered_orders_count' => $delivered,
            'cancel_orders_count' => $canceled,
            'orders_count' => $orders,
        ]);
    }
}
