<?php

namespace App\Services\OrderService;

use App\Helpers\ResponseError;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Shop;
use App\Models\Stock;
use App\Services\CoreService;

class OrderDetailService extends CoreService
{
    private float $rate;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    protected function getModelClass()
    {
        return OrderDetail::class;
    }

    public function create($order, $collection): bool
    {
        $this->rate = $order->rate;
        $order->orderDetails()->delete();
        foreach ($collection as $item) {

            /** @var Shop $shop */
            $shop = Shop::with('subscription')->find($item['shop_id']);

            if (!$shop) {
                continue;
            }

            $commissionFee = $shop->subscription ? 0 :
                (collect($item['products'])->sum('total_price') / 100) * $shop->percentage;

            $detail = $order->orderDetails()->create($this->setDetailParams($item + ['commission_fee' => $commissionFee]));
            if ($detail) {


                $detail->orderStocks()->delete();

                foreach ($item['products'] as $product) {
                    $detail->orderStocks()->create($this->setProductParams($product));
                }
            }
        }
        return true;
    }

    public function updateStatus(int $id, $status)
    {
        if (!isset($status)) {
            return ['status' => false, 'code' => ResponseError::ERROR_400];
        }
        $detail = $this->model()->find($id);
        if ($detail) {
            // Order Status change logic
            return (new OrderStatusUpdateService())->statusUpdate($detail, $status);
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    private function setDetailParams($detail)
    {
        return [
            'shop_id' => $detail['shop_id'],
            'price' => round(collect($detail['products'])->sum('total_price')  / $this->rate, 2),
            'tax' => round($detail['tax'] / $this->rate, 2) ,
            'commission_fee' => round($detail['commission_fee'] / $this->rate, 2) ,
            'status' => $detail['status'] ?? Order::NEW,
            'delivery_type_id' => $detail['delivery_type_id'] ?? null,
            'delivery_address_id' => $detail['delivery_address_id'] ?? null,
            'deliveryman' => $detail['deliveryman'] ?? null,
            'delivery_date' => $detail['delivery_date'] ?? null,
            'delivery_time' => $detail['delivery_time'] ?? null,
            'point_delivery_id' => $detail['point_delivery_id'] ?? null,
        ];
    }

    private function setProductParams($product)
    {
        return [
            'stock_id' => $product['id'],
            'origin_price' => round($product['price'] / $this->rate, 2),
            'total_price' => round($product['total_price'] / $this->rate, 2),
            'tax' => round($product['tax'] / $this->rate, 2),
            'discount' => round($product['discount'] / $this->rate, 2),
            'quantity' => $product['qty'],
        ];
    }


}
