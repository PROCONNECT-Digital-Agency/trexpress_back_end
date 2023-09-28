<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PushNotification;
use App\Models\User;
use App\Repositories\Interfaces\OrderRepoInterface;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\OrderService\OrderReviewService;
use App\Services\OrderService\OrderStatusUpdateService;
use App\Traits\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends UserBaseController
{
    use Notification;

    private OrderRepoInterface $orderRepository;
    private OrderServiceInterface $orderService;

    /**
     * @param OrderRepoInterface $orderRepository
     * @param OrderServiceInterface $orderService
     */
    public function __construct(OrderRepoInterface $orderRepository, OrderServiceInterface $orderService)
    {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
    }


    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request)
    {
        $orders = $this->orderRepository->ordersPaginate($request->perPage ?? 15,
            auth('sanctum')->id(), $request->all());
        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $result = $this->orderService->create($request->merge(['user_id' => auth('sanctum')->id()]));
        if ($result['status']) {

            $admins = User::whereHas('roles', function ($q) {
                $q->whereIn('role_id', [99, 21]);
            })->whereNotNull('firebase_token')->get();

            $sellers = User::whereHas('shop', function ($q) use ($result) {
                $q->whereIn('id', $result['data']->orderDetails()->pluck('shop_id'));
            })->whereNotNull('firebase_token')->get();

            $this->sendNotification(
                array_merge($admins->pluck('firebase_token')->toArray(),
                    $sellers->pluck('firebase_token')->toArray()),
                "New order was created",
                $result['data']->id,
                data_get($result, 'data')?->setAttribute('type', PushNotification::NEW_ORDER)?->only(['id', 'status', 'type']),
                array_merge($admins->pluck('id')->toArray(),
                    $sellers->pluck('id')->toArray())
            );

            return $this->successResponse(__('web.record_was_successfully_create'), $result['data']);
        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id)
    {
        $order = $this->orderRepository->orderDetails($id);
        if ($order && $order->user_id == auth('sanctum')->id()) {
            return $this->successResponse(ResponseError::NO_ERROR, OrderResource::make($order));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
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
    public function addOrderReview(int $id, Request $request)
    {
        $result = (new OrderReviewService())->addReview($id, $request);
        if ($result['status']) {
            return $this->successResponse(ResponseError::NO_ERROR, OrderResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }


    public function orderStatusChange(Request $request, int $id)
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
        if ($request->status == Order::CANCELED) {
            $user = $order->user;
            if ($user->wallet && data_get($order->transaction()->where('status', 'paid')->first(), 'id')) {
                $user->wallet()->update(['price' => $user->wallet->price + ($order->price / $order->currency->rate + $order->tax / $order->currency->rate)]);
            }

        }
        foreach ($order->orderDetails as $detail) {
            $this->orderDetailStatusChange($detail->id, $request);
        }
        $data = Order::with('orderDetails')->find($id);

        return $this->successResponse(ResponseError::NO_ERROR, $data);

    }

    /**
     * Add Review to OrderDetails.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function orderDetailStatusChange(int $id, Request $request)
    {
        if (!isset($request->status) || $request->status != Order::CANCELED) {
            return $this->errorResponse(ResponseError::ERROR_253, trans('errors.' . ResponseError::ERROR_253, [], $this->language ?? config('app.locale')),
                Response::HTTP_BAD_REQUEST
            );
        }
        $detail = OrderDetail::find($id);

        if ($detail) {
            $result = (new OrderStatusUpdateService())->statusUpdate($detail, $request->status);
            if ($result['status']) {
                return $this->successResponse(ResponseError::NO_ERROR, OrderResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }


}
