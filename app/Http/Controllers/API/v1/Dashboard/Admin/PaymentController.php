<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\PaymentResource;
use App\Repositories\PaymentRepository\PaymentRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends AdminBaseController
{
    private PaymentRepository $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        parent::__construct();
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request)
    {
        $payments = $this->paymentRepository->paymentsList($request->all());
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
        if ($payment) {
            return $this->successResponse(__('web.payment_found'), PaymentResource::make($payment->load('translations')));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param FilterParamsRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function update(FilterParamsRequest $request, $id)
    {
        $payment = $this->paymentRepository->paymentDetails($id);
        if ($payment) {
            $result = $payment->update([
                'client_id' => $request->client_id ?? null,
                'secret_id' => $request->secret_id ?? null,
                'sandbox' => $request->sandbox ?? 0,
            ]);
            if ($result) {
                $payment->translations()->delete();
                foreach ($request->title as $index => $title) {
                    $payment->translation()->create([
                        'locale' => $index,
                        'title' => $title,
                        'client_title' => $request->client_title[$index] ?? null,
                        'secret_title' => $request->secret_title[$index] ?? null,
                    ]);
                }
            }
            return $this->successResponse(__('web.record_has_been_successfully_updated'), PaymentResource::make($payment));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Set Model Active.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function setActive(int $id)
    {
        $payment = $this->paymentRepository->paymentDetails($id);
        if ($payment) {
            $payment->update(['active' => !$payment->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), PaymentResource::make($payment));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
