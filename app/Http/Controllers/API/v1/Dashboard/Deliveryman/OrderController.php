<?php

namespace App\Http\Controllers\API\v1\Dashboard\Deliveryman;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Repositories\OrderRepository\OrderRepository;
use App\Services\OrderService\OrderService;
use App\Services\OrderService\OrderStatusUpdateService;
use App\Traits\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\DeliveryMan\Order\ReportRequest;


class OrderController extends DeliverymanBaseController
{
    use Notification;
    private OrderRepository $orderRepository;
    private Order $model;
    private OrderService $service;

    public function __construct(OrderRepository $orderRepository, Order $model,OrderService $service)
    {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->model = $model;
        $this->service = $service;
        $this->lang = \request('lang') ?? 'en';
    }

    public function paginate(Request $request)
    {
        /** @var User $user */
        $user = auth('sanctum')->user();
        $filter = $request->all();
        $filter['deliveryman'] = auth('sanctum')->id();

        unset($filter['isset-deliveryman']);

        if (data_get($filter, 'empty-deliveryman')) {
            $filter['shop_ids'] = $user->invitations->pluck('shop_id')->toArray();
            unset($filter['deliveryman']);
        }

        $orderDetails = $this->orderRepository->ordersPaginate(perPage: $request->perPage ?? 10, array: $filter);

        return OrderResource::collection($orderDetails);
    }

    public function show(int $id)
    {
        $order = $this->model
            ->with([
                'user', 'review', 'point',
                'currency' => function ($q) {
                    $q->select('id', 'title', 'symbol');
                },
                'orderDetails.deliveryType.translation' => fn($q) => $q->actualTranslation($this->lang),
                'orderDetails.deliveryAddress',
                'orderDetails.deliveryMan',
                'coupon',
                'userAddress',
                'delivery.translation' => fn($q) => $q->actualTranslation($this->lang),
                'orderDetails.shop.translation' => fn($q) => $q->actualTranslation($this->lang),
                'transaction.paymentSystem' => function ($q) {
                    $q->select('id', 'tag', 'active');
                },
                'transaction.paymentSystem.translation' => function ($q) {
                    $q->select('id', 'locale', 'payment_id', 'title')->actualTranslation($this->lang);
                },
                'orderDetails.orderStocks.stock.stockExtras.group.translation' => function ($q) {
                    $q->select('id', 'extra_group_id', 'locale', 'title')->actualTranslation($this->lang);
                },
                'orderDetails.orderStocks.stock.countable.translation' => function ($q) {
                    $q->select('id', 'product_id', 'locale', 'title')->actualTranslation($this->lang);
                },])
            ->where('deliveryman_id',auth('sanctum')->id())
            ->find($id);
        if ($order){
            return $this->successResponse(__('web.order_found'), OrderResource::make($order));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update Order Status details by OrderDetail ID.
     *
     * @param FilterParamsRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function statusChange(Request $request, int $id): JsonResponse
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
        }elseif (!in_array($request->status,[Order::DELIVERED,Order::CANCELED])){
            return $this->errorResponse(ResponseError::ERROR_253,
                trans('errors.' . ResponseError::ERROR_253, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_BAD_REQUEST
            );
        }

        $order->update(['status' => $request->status]);

        foreach ($order->orderDetails as $detail) {
            (new OrderStatusUpdateService())->statusUpdate($detail,$request->status);
        }
        $data = Order::with('orderDetails')->find($id);

        return $this->successResponse(ResponseError::NO_ERROR, $data);

    }

    /**
     * Display the specified resource.
     *
     * @param int|null $id
     * @return JsonResponse
     */
    public function orderDeliverymanUpdate(?int $id): JsonResponse
    {
        $result = $this->service->attachDeliveryMan($id);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(__('web.delivery_man_setting_found'), OrderResource::make(data_get($result, 'data'))
        );
    }

    public function report(ReportRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['deliveryman'] = auth('sanctum')->id();

        return $this->successResponse(
            __('web.report_found'),
            $this->orderRepository->deliveryManReport($validated)
        );
    }
}
