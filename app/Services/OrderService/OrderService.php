<?php

namespace App\Services\OrderService;

use App\Helpers\ResponseError;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Point;
use App\Models\User;
use App\Services\CoreService;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\ProductService\StockService;
use Exception;
use Throwable;

class OrderService extends CoreService implements OrderServiceInterface
{
    protected function getModelClass()
    {
        return Order::class;
    }

    public function create($collection)
    {
        try {
            $collection->rate = Currency::where('id', $collection->currency_id)->first()->rate;

            $order = $this->model()->create($this->setOrderParams($collection));

            if ($order) {
//                dd($collection->shops);
                $this->checkCoupon($collection['coupon'] ?? null, $order);

                $cashback = Point::getActualPoint($order->price);

                if ($cashback){

                    $order->update([
                        'cash_back' => $cashback
                    ]);

//                    $this->walletTopUp($collection, $order);
                }

                (new OrderDetailService)->create($order, $collection->shops);

                (new StockService)->decrementStocksQuantity($collection->shops);

                return ['status' => true, 'message' => ResponseError::NO_ERROR, 'data' => Order::query()->find(data_get($order, 'id'))];
            }

            return ['status' => false, 'code' => ResponseError::ERROR_501];
        } catch (Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }

    }

    public function update(int $id, $collection)
    {
        try {
            $order = $this->model()->find($id);
            if ($order) {
                $order->update($this->setOrderParams($collection));
                (new StockService)->incrementStocksQuantity($order->load('orderDetails')->orderDetails);
                (new OrderDetailService)->create($order, $collection->shops);
                (new StockService)->decrementStocksQuantity($collection->shops);

                return ['status' => true, 'message' => ResponseError::NO_ERROR, 'data' => $order];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        } catch (Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    private function setOrderParams($collection): array
    {
        return [
            'user_id' => $collection->user_id,
            'price' => round($collection->total / $collection->rate, 2),
            'currency_id' => $collection->currency_id ?? Currency::whereDefault(1)->pluck('id')->first(),
            'rate' => $collection->rate,
            'note' => $collection->note ?? null,
            'status' => $collection->status ?? Order::NEW,
            'total_delivery_fee' => round($collection->delivery_fee / $collection->rate, 2) ?? null,
            'tax' => $collection->tax ?? null,
            'user_address_id' => $collection->user_address_id,
            'delivery_id' => $collection->delivery_id,
            'cash_back' => $collection->cash_back ?? null
        ];
    }

    private function checkCoupon($coupon, $order){

        if (isset($coupon)) {
            $result = Coupon::checkCoupon($coupon)->first();
            if ($result) {
                $couponPrice = match ($result->type) {
                    'percent' => ($order->price / 100) * $result->price,
                    default => $result->price,
                };
                $order->update(['price' => $order->price - $couponPrice]);

                $order->coupon()->create([
                    'user_id' => $order->user_id,
                    'name' => $result->name,
                    'price' => $couponPrice,
                ]);
                $result->decrement('qty');
            }
        }
    }

//    private function checkCashback($coupon, $order){
//
//        if (isset($coupon)) {
//            $result = Coupon::checkCoupon($coupon)->first();
//            if ($result) {
//                switch ($result->type) {
//                    case 'percent':
//                        $couponPrice = ($order->price / 100) * $result->price;
//                        break;
//                    default:
//                        $couponPrice = $result->price;
//                        break;
//                }
//                $order->update(['price' => $order->price - $couponPrice]);
//
//                $order->coupon()->create([
//                    'user_id' => $order->user_id,
//                    'name' => $result->name,
//                    'price' => $couponPrice,
//                ]);
//                $result->decrement('qty');
//            }
//        }
//    }

    /**
     * @param int|null $id
     * @return array
     */
    public function attachDeliveryMan(?int $id): array
    {
        /** @var Order $order */
        /** @var User $user */
        try {
            $user = auth('sanctum')->user();
            $order = Order::with('user')->find($id);

            if (empty($order) || ($order?->deliveryType?->type == Delivery::TYPE_PICKUP)) {
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_404,
                    'message' => 'Invalid deliveryman or token not found'
                ];
            }

            if (!empty($order->deliveryman)) {
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_210,
                    'message' => 'Delivery already attached'
                ];
            }
//
//            if (!$user?->invitations?->where('shop_id', $order->shop_id)?->first()?->id) {
//                return [
//                    'status' => false,
//                    'code' => ResponseError::ERROR_212,
//                    'message' => 'Not your shop. Check your other account'
//                ];
//            }

            $orderCount = Order::where('deliveryman', $user->id)->whereIn('status', '!=', [
                Order::DELIVERED,
                Order::CANCELED,
            ])->count();

            if ($user?->deliveryManSetting?->order_quantity > $orderCount) {
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_213,
                    'message' => 'Your order amount is full'
                ];
            }

            $order->update([
                'deliveryman' => auth('sanctum')->id(),
            ]);

            return ['status' => true, 'message' => ResponseError::NO_ERROR, 'data' => $order];
        } catch (Throwable) {
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => ResponseError::ERROR_501];
        }
    }


}
