<?php
namespace App\Services\OrderService;

use App\Helpers\ResponseError;
use App\Jobs\PayReferral;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderProduct;
use App\Models\OrderRefund;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\CoreService;
use App\Services\WalletHistoryService\WalletHistoryService;
use DB;
use Exception;
use Throwable;

class OrderRefundService extends CoreService
{
    protected function getModelClass(): string
    {
        return OrderRefund::class;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {
            $exist = OrderRefund::where('order_id', data_get($data,'order_id'))->first();

            if (in_array(data_get($exist, 'status'), [OrderRefund::STATUS_PENDING, OrderRefund::STATUS_ACCEPTED])) {
                return [
                    'status'    => false,
                    'code'      => ResponseError::ERROR_506,
                    'message'   => __('errors.' . ResponseError::ERROR_506, locale: $this->language),
                ];
            }

            /** @var OrderRefund $orderRefund */
            $orderRefund = $this->model();

            $orderRefund->create($data);

            if (data_get($data, 'images.0')) {
                $orderRefund->uploads(data_get($data, 'images'));
            }

            return ['status' => true, 'message' => ResponseError::NO_ERROR];

        } catch (Throwable $e) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_501,
                'message' => __('errors.' . ResponseError::ERROR_501, locale: $this->language),
            ];
        }
    }

    public function update(OrderRefund $orderRefund, array $data): array
    {
        try {

            if ($orderRefund->status == data_get($data, 'status')) {
                return [
                    'status' => false,
                    'code'   => __('errors.' . ResponseError::ERROR_252, locale: $this->language)
                ];
            }

            $orderRefund = $orderRefund->loadMissing([
                'order.shop:id,uuid,user_id',
                'order.shop.seller:id',
                'order.shop.seller.wallet:id,user_id,uuid',
                'order.deliveryMan:id',
                'order.deliveryMan.wallet:id,user_id,uuid',
                'order.user:id',
                'order.user.wallet:id,user_id,uuid',
                'order.transactions',
                'order.orderDetails:id,order_id,stock_id,quantity',
                'order.orderDetails.stock',
            ]);

            /** @var User $user */
            $user = data_get($orderRefund->order, 'user');

            /** @var Transaction $transaction */
            $transaction = $orderRefund?->order
                ?->transactions()
                ?->where('status', Transaction::PAID)
                ?->first();

            $payment  = data_get(Payment::where('tag', '!=', 'cash')->first(), 'id');
            $isWallet = $payment && optional($orderRefund->order)->transactions()
                    ?->where('payment_sys_id', '!=', $payment)
                    ?->where('status', Transaction::PAID)
                    ?->first();

            if (data_get($data, 'status') === OrderRefund::STATUS_ACCEPTED && $isWallet) {

                if (!$user->wallet) {
                    return [
                        'status'  => false,
                        'message' => __('errors.' . ResponseError::ERROR_108, locale: $this->language),
                        'code'    => ResponseError::ERROR_108
                    ];
                }

                if (!$orderRefund->order) {
                    return [
                        'status'  => false,
                        'message' => __('errors' . ResponseError::ORDER_NOT_FOUND, locale: $this->language),
                        'code'    => ResponseError::ERROR_404
                    ];
                }

                /** @var Transaction $existRefund */
                $existRefund = $orderRefund->order->transactions()
                    ->where('status', Transaction::REFUND)
                    ->first();

                if ($existRefund) {
                    return [
                        'status'  => false,
                        'code'    => ResponseError::ERROR_501,
                        'message' => __('errors.' . ResponseError::ORDER_REFUNDED, locale: $this->language),
                    ];
                }
            }

            DB::transaction(function () use ($orderRefund, $data, $user, $transaction) {

                $orderRefund->update($data);

                if (data_get($data, 'images.0')) {
                    $orderRefund->galleries()->delete();
                    $orderRefund->uploads(data_get($data, 'images'));
                }

                if ($orderRefund->status !== OrderRefund::STATUS_ACCEPTED && !$transaction?->id) {
                    return true;
                }

                $order = $orderRefund->order;

                if (!$user->wallet?->id) {
                    throw new Exception(__('errors.' . ResponseError::ERROR_108, locale: $this->language));
                }

                if ($order->transactions->where('status', Transaction::PAID)->first()?->id) {

                    (new WalletHistoryService)->create($user,[
                        'type'   => 'topup',
                        'price'  => $order->price,
                        'note'   => "For Order #$order->id",
                        'status' => WalletHistory::PAID,
                    ]);

//                    if ($order->status === Order::DELIVERED) {
//
//                        if (!$order->shop?->seller?->wallet?->id) {
//                            throw new Exception(__('errors.' . ResponseError::ERROR_114, locale: $this->language));
//                        }
//
//                        (new WalletHistoryService)->create($user,[
//                            'type'   => 'withdraw',
//                            'price'  => $order->price,
//                            'note'   => "For Order #$order->id",
//                            'status' => WalletHistory::PAID,
//                            'user'   => $order->shop->seller
//                        ]);
//
//                        if (!in_array($order->delivery->type,[Delivery::TYPE_FREE,Delivery::TYPE_PICKUP]) && $order->deliveryMan?->wallet?->id) {
//
//                            (new WalletHistoryService)->create($user,[
//                                'type'   => 'withdraw',
//                                'price'  => $order->delivery_fee,
//                                'note'   => "For Order #$order->id",
//                                'status' => WalletHistory::PAID,
//                                'user'   => $order->deliveryMan
//                            ]);
//
////                          if(!$order->deliveryMan?->wallet?->id) {
////                              throw new Exception(__('errors.' . ResponseError::ERROR_113, locale: $this->language));
////                          }
//
//                        }
//
//                    }

                }

                $order->orderDetails->map(function (OrderDetail $orderDetail) {
                    $orderDetail->map(function (OrderProduct $orderProduct){
                        $orderProduct->stock()->increment('quantity', $orderProduct->quantity);
                    });
                });

//                if ($order->status === Order::STATUS_DELIVERED) {
//                    PayReferral::dispatchAfterResponse($order->user, 'decrement');
//                }

                return true;
            });

            return ['status' => true, 'message' => ResponseError::NO_ERROR];

        } catch (Throwable $e) {
//            $this->error($e);

            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => $e->getMessage()];
        }
    }

    public function delete(?array $ids = [], ?int $shopId = null, ?bool $isAdmin = false): array
    {
        try {

            foreach (OrderRefund::find(is_array($ids) ? $ids : []) as $orderRefund) {

                if (!$isAdmin) {
                    if (empty($shopId) && data_get($orderRefund->order, 'user_id') !== auth('sanctum')->id()) {
                        continue;
                    } else if (!in_array($orderRefund->status, [OrderRefund::STATUS_ACCEPTED, OrderRefund::STATUS_CANCELED])) {
                        continue;
                    }
//                    else if(!empty($shopId) && $orderRefund->order?->shop_id !== $shopId) {
//                        continue;
//                    }
                }

                $orderRefund->galleries()->delete();
                $orderRefund->delete();
            }

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
            ];

        } catch (Throwable $e) {

            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_503,
                'message' => __('errors.' . ResponseError::ERROR_503, locale: $this->language),
            ];
        }
    }

}
