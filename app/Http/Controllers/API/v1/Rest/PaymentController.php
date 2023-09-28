<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Repositories\PaymentRepository\PaymentRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends RestBaseController
{
    private Payment $model;
    private PaymentRepository $paymentRepository;

    /**
     * @param Payment $model
     * @param PaymentRepository $paymentRepository
     */
    public function __construct(Payment $model, PaymentRepository $paymentRepository)
    {
        $this->model = $model;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $payments = $this->paymentRepository->paginate(['active' => 1]);
        return PaymentResource::collection($payments);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $payment = $this->paymentRepository->paymentDetails($id);
        if ($payment && $payment->active){
            return $this->successResponse(__('web.payment_found'), PaymentResource::make($payment));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang ?? 'en'),
            Response::HTTP_NOT_FOUND
        );
    }

}
