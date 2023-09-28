<?php

namespace App\Services\OrderService;

use App\Helpers\ResponseError;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderProduct;
use App\Services\CoreService;
use Exception;

class OrderStatusUpdateService extends CoreService
{

    /**
     * @return mixed
     */
    protected function getModelClass()
    {
        return OrderDetail::class;
    }

    public function statusUpdate($detail, $status)
    {

        if ($detail->status == $status) {
            return ['status' => false, 'code' => ResponseError::ERROR_252];
        } elseif ($detail->status == Order::CANCELED) {
            return ['status' => false, 'code' => ResponseError::ERROR_254];
        }

        try {
            $detail->update(['status' => $status]);

            $orderDetailCount = OrderDetail::where('order_id',$detail->order_id)->count();

            $orderDetailStatusCount = OrderDetail::where('status',$status)->where('order_id',$detail->order_id)->count();

            if (($orderDetailCount == $orderDetailStatusCount) || ($orderDetailCount == 1)){
                $order = Order::find($detail->order_id);

                $order->update([
                    'status' => $status
                ]);
            }

            if ($status == Order::CANCELED) {
                $detail->orderStocks->map(function (OrderProduct $orderProduct) {
                    $orderProduct->stock()->increment('quantity', $orderProduct->quantity);
                });

            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $detail];
        } catch (Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }


}
