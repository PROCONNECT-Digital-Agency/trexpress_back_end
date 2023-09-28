<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\Seller\Order\OrderReportRequest;
use App\Http\Requests\ReportChartRequest;
use App\Http\Requests\ReportPaginateRequest;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PushNotification;
use App\Models\User;
use App\Repositories\Interfaces\OrderRepoInterface;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\OrderService\OrderDetailService;
use App\Traits\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends SellerBaseController
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
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|JsonResponse
     */
    public function paginate(FilterParamsRequest $request)
    {
        if ($this->shop) {
            $orders = $this->orderRepository->sellerOrdersPaginate(
                $request->perPage ?? 15, $request->user_id ?? null,
                $request->merge(['shop_id' => $this->shop->id])->all());

            return OrderResource::collection($orders);
        }  else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        if ($this->shop) {
            $result = $this->orderService->create($request);
            if ($result['status']) {

                $admins = User::whereHas('roles', function ($q) {
                    $q->whereIn('role_id', [99, 21]);
                })->whereNotNull('firebase_token')->get();

                // Select Admins Firebase Token to Push Notification
                Log::info("ADMIN NOTIFICATION", $admins->toArray());

                $this->sendNotification(
                    $admins->pluck('firebase_token')->toArray(),
                    "New order was created",
                    $result['data']->id,
                    data_get($result, 'data')?->setAttribute('type', PushNotification::NEW_ORDER)?->only(['id', 'status', 'type']),
                    $admins->pluck('id')->toArray()
                );

                return $this->successResponse( __('web.record_was_successfully_create'), OrderResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        }  else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id)
    {
        if ($this->shop) {
            $order = $this->orderRepository->orderDetails($id, $this->shop->id);
            if ($order) {
                return $this->successResponse(__('web.order_found'), OrderResource::make($order));
            }
            return $this->errorResponse(
                ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND
            );
        }  else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(int $id, Request $request)
    {
        if ($this->shop) {
//            $order = Order::whereHas('orderDetails', function ($q) {
//                $q->where('shop_id', $this->shop->id);
//            })->find($id);

            $result = $this->orderService->update($id, $request);
            if ($result['status']) {
                return $this->successResponse(__('web.record_was_successfully_create'), OrderResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function allOrderStatusChange(Request $request, int $id)
    {

        $order = Order::find($id);

        if ($order->status == Order::CANCELED) {
            return $this->errorResponse(ResponseError::ERROR_254,
                trans('errors.' . ResponseError::ERROR_254, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($order->status == Order::READY) {
            return $this->errorResponse(ResponseError::ERROR_252,
                trans('errors.' . ResponseError::ERROR_252, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_BAD_REQUEST
            );
        }


        foreach ($order->orderDetails as $detail) {
            (new OrderDetailService())->updateStatus($detail->id, $request);
        }
        $data = Order::with('orderDetails')->find($id);

        return $this->successResponse(ResponseError::NO_ERROR, $data);

    }

    /**
     * Update Order Status details by OrderDetail ID.
     *
     * @param int $orderDetail
     * @param Request $request
     * @return JsonResponse
     */
    public function orderDetailStatusUpdate(int $orderDetail, Request $request)
    {
        if ($this->shop) {
            $order = OrderDetail::where('shop_id', $this->shop->id)->find($orderDetail);
            if ($order) {
                $result = (new OrderDetailService())->updateStatus($orderDetail, $request->status ?? null);
                if ($result['status']) {
                    // Select User Firebase Token to Push Notification
                    $user = User::where('id', $result['data']->order->user_id)->pluck('firebase_token');

                    $this->sendNotification(
                        $user->pluck('firebase_token')->toArray(),
                        "Your order status has been changed to $request->status",
                        $result['data']->order->id,
                        data_get($result, 'data')?->setAttribute('type', PushNotification::NEW_ORDER)?->only(['id', 'status', 'type']),
                        $user->pluck('id')->toArray()
                    );
                    return $this->successResponse( __('errors.' . ResponseError::NO_ERROR), OrderDetailResource::make($result['data']));
                }
                return $this->errorResponse(
                    $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang ?? 'en'),
                    Response::HTTP_BAD_REQUEST
                );
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang ?? 'en'),
                Response::HTTP_BAD_REQUEST
            );

        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Update Order Delivery details by OrderDetail ID.
     *
     * @param int $orderDetail
     * @param Request $request
     * @return JsonResponse
     */
    public function orderDetailDeliverymanUpdate(int $orderDetail, Request $request)
    {
        if ($this->shop) {
            $orderDetail = OrderDetail::where('shop_id', $this->shop->id)->find($orderDetail);
            if ($orderDetail) {
                $orderDetail->update([
                    'deliveryman' => $request->deliveryman ?? $orderDetail->deliveryman,
                ]);
                return $this->successResponse(__('web.record_has_been_successfully_updated'), OrderDetailResource::make($orderDetail));
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang ?? 'en'),
                Response::HTTP_NOT_FOUND
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function ordersReportChart(ReportChartRequest $request)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->orderRepository->orderReportChartCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function ordersReportPaginate(ReportPaginateRequest $request, FilterParamsRequest $filterParamsRequest)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->orderRepository->ordersReportPaginate($filterParamsRequest->get('perPage', 15));

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }


    public function report(OrderReportRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['shop_id'] = $this->shop->id;

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            $this->orderRepository->report($validated)
        );
    }

}
