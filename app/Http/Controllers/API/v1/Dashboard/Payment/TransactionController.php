<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\SubscriptionResource;
use App\Http\Resources\WalletResource;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\TransactionService\TransactionService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends PaymentBaseController
{
    private Transaction $model;

    /**
     * @param Transaction $model
     */
    public function __construct(Transaction $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    public function store(string $type, int $id, FilterParamsRequest $request)
    {
        switch ($type) {
            case 'order':
                $result = (new TransactionService())->orderTransaction($id, $request);
                if ($result['status']) {
                    return $this->successResponse(__('web.record_successfully_created'),  $result['data']);
                } else {
                    return $this->errorResponse(
                        $result['code'], __('errors.' . $result['code'], [], \request()->lang ?? 'en'),
                        Response::HTTP_BAD_REQUEST
                    );
                }
            case 'subscription':
                $result = (new TransactionService())->subscriptionTransaction($id, $request);
                if ($result['status']) {
                    return $this->successResponse(__('web.record_successfully_created'), SubscriptionResource::make($result['data']));
                } else {
                    return $this->errorResponse(
                        $result['code'], __('errors.' . $result['code'], [], \request()->lang ?? 'en'),
                        Response::HTTP_BAD_REQUEST
                    );
                }
            default:
                $result = (new TransactionService())->walletTransaction($id, $request);
                if ($result['status']) {
                    return $this->successResponse(__('web.record_successfully_created'), WalletResource::make($result['data']));
                } else {
                    return $this->errorResponse(
                        $result['code'], __('errors.' . $result['code'], [], \request()->lang ?? 'en'),
                        Response::HTTP_BAD_REQUEST
                    );
                }
        }
    }

    public function updateStatus(string $type, int $id, Request $request)
    {

        $order = Order::find($id);
        if ($order) {
            $this->model->where('payable_id', $order->id)->update([
                'status' => $request->status,
                'payment_trx_id' => $request->payment_trx_id,
            ]);

            return $this->successResponse(__('web.record_successfully_created'), OrderResource::make($order));
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], \request()->lang ?? 'en'),
                Response::HTTP_NOT_FOUND
            );
        }
    }
}
