<?php

namespace App\Services\OrderService;

use App\Helpers\ResponseError;
use App\Models\Order;
use App\Models\OrderDetail;

class OrderReviewService extends \App\Services\CoreService
{

    protected function getModelClass()
    {
        return Order::class;
    }

    public function addReview($id, $collection){
        $order = $this->model()->find($id);
        if ($order){
            if ($order->status == Order::DELIVERED){
                $order->addReview($collection);
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $order];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_432];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }
}
