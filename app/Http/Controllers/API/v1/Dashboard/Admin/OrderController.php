<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\ReportChartRequest;
use App\Http\Requests\ReportPaginateRequest;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Point;
use App\Models\PushNotification;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Interfaces\OrderRepoInterface;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\OrderService\OrderStatusUpdateService;
use App\Services\WalletHistoryService\WalletHistoryService;
use App\Traits\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends AdminBaseController
{
    use Notification;
    private OrderRepoInterface $orderRepository;
    private OrderServiceInterface $orderService;

    /**
     * @param OrderRepoInterface $orderRepository
     * @param OrderServiceInterface $orderService
     */
    public function __construct(
        OrderRepoInterface $orderRepository,
        OrderServiceInterface $orderService,
    )
    {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $orders = $this->orderRepository->ordersList();

        return OrderResource::collection($orders);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request)
    {
        $orders = $this->orderRepository->ordersPaginate($request->perPage ?? 15, $request->user_id ?? null, $request->all());

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $result = $this->orderService->create($request);
        if ($result['status']) {

            // Select Seller Firebase Token to Push Notification
            $sellers = User::whereHas('shop', function ($q) use($result){
                $q->whereIn('id', $result['data']->orderDetails()->pluck('shop_id'));
            })->whereNotNull('firebase_token')->get();

            $this->sendNotification(
                $sellers->pluck('firebase_token')->toArray(),
                "New order was created",
                $result['data']->id,
                data_get($result, 'data')?->setAttribute('type', PushNotification::NEW_ORDER)?->only(['id', 'status', 'type']),
                $sellers->pluck('id')->toArray()
            );

            return $this->successResponse( __('web.record_was_successfully_create'), OrderResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id)
    {
        $order = $this->orderRepository->orderDetails($id);
        if ($order) {
            return $this->successResponse(__('web.language_found'), OrderResource::make($order));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    public function allOrderStatusChange(Request $request, int $id)
    {
        $order = Order::find($id);
        if ($order->status == $request->status) {
            return $this->errorResponse(ResponseError::ERROR_252,
                trans('errors.' . ResponseError::ERROR_252, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_BAD_REQUEST
            );
        } elseif ($order->status == Order::CANCELED) {
            return $this->errorResponse(ResponseError::ERROR_254,
                trans('errors.' . ResponseError::ERROR_254, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_BAD_REQUEST
            );
        }



        $order->update(['status' => $request->status]);

        // Select Seller Firebase Token to Push Notification
        $user = $order->user;

        $this->sendNotification(
            [$user->firebase_token],
            "Your order status has been changed to ".$order->status,
            $order->id,
            $order->setAttribute('type', PushNotification::STATUS_CHANGED)?->only(['id', 'status', 'type']),
            [$user->id]
        );

        if ($order->status == Order::DELIVERED){
            $this->userCashbackTopUp($order, $order->user);

            // DELIVERYMAN TOP UP
            if (isset($order->deliveryMan)) {
                $this->deliverymanWalletTopUp($order);
            }
            $request->merge(['status' => Order::DELIVERED]);

        }

        if ($order->status == Order::CANCELED) {

            $user = $order->user;

            if ($user->wallet && data_get($order->transaction()->where('status', 'paid')->first(), 'id')) {
                $user->wallet()->update(['price' => $user->wallet->price + ($order->price + $order->tax)]);
            }
        }

        foreach ($order->orderDetails as $detail) {
            $this->orderStatusChange($detail->id, $request);
        }
        $data = Order::with('orderDetails')->find($id);

        return $this->successResponse(ResponseError::NO_ERROR, $data);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(int $id, Request $request)
    {
        $result = $this->orderService->update($id, $request);
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_create'), OrderResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Update Order Delivery details by OrderDetail ID.
     *
     * @param int $orderDetail
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderDeliverymanUpdate(int $orderId, Request $request)
    {
        $order = $this->orderRepository->orderDetails($orderId);
        if ($order){
            $order->update([
                'deliveryman_id' => $request->deliveryman_id ?? $order->deliveryman_id,
            ]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), OrderDetailResource::make($order));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Add Review to OrderDetails.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function orderStatusChange(int $id, Request $request)
    {
        $detail = OrderDetail::find($id);
        if ($detail) {
            $result = (new OrderStatusUpdateService())->statusUpdate($detail, $request->status);
            if ($result['status']) {
                return $this->successResponse(ResponseError::NO_ERROR, OrderResource::make($detail));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    public function ordersReportChart()
    {
        try {
            $result = $this->orderRepository->orderReportChartCache();
            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function ordersReportPaginate(FilterParamsRequest $filterParamsRequest)
    {
        try {
            $result = $this->orderRepository->ordersReportPaginate($filterParamsRequest->get('perPage', 15));
            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    // User Point topup function
    private function userCashbackTopUp($order, $user)
    {
        $price = Point::getActualPoint($order->price);
        if ($price > 0) {
            $user->wallet()->update(['price' => $user->wallet->price + $price]);

            $request = request()->merge([
                'type' => 'topup',
                'price' => $price,
                'note' => 'Cashback for Order #' . $order->id,
                'status' => 'paid',
            ]);

            $order->createTransaction([
                'price' => $price,
                'user_id' => $order->user_id,
                'payment_sys_id' => Payment::where('tag',Payment::WALLET)->first()->id,
                'payment_trx_id' => null,
                'note' => $order->id,
                'perform_time' => now(),
                'status' => Transaction::PAID,
                'status_description' => 'Transaction for Cashback #' . $order->id
            ]);

            return (new WalletHistoryService())->create($user, $request);
        }
    }

    // Deliveryman  Order price topup function
    private function deliverymanWalletTopUp($order): array
    {
        $deliveryman = $order->deliveryMan;
        $deliveryman?->wallet()->update(['price' => $deliveryman?->wallet?->price + $order->total_delivery_fee]);

        $request = request()->merge([
            'type' => 'topup',
            'price' => $order->total_delivery_fee,
            'note' => 'For Order #' . $order->id,
            'status' => 'paid',
        ]);
        return (new WalletHistoryService())->create($deliveryman, $request);
    }
}
