<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\SMSGatewayResource;
use App\Models\SmsGateway;
use Symfony\Component\HttpFoundation\Response;

class SMSGatewayController extends AdminBaseController
{
    private SmsGateway $model;

    public function __construct(SmsGateway $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request)
    {
        $gateways = $this->model->all();
        return SMSGatewayResource::collection($gateways);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $gateway = $this->model->find($id);
        if ($gateway) {
            return $this->successResponse(__('web.sms_gateway_found'), SMSGatewayResource::make($gateway));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang ?? 'en'),
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
        $gateway = $this->model->find($id);
        if ($gateway) {
            $gateway->update([
                'from' => $request->from ?? null,
                'api_key' => $request->api_key ?? null,
                'secret_key' => $request->secret_key ?? null,
                'service_id' => $request->service_id ?? null,
                'text' => $request->text ?? null,
            ]);

            return $this->successResponse(__('web.record_has_been_successfully_updated'), SMSGatewayResource::make($gateway));
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
        $gateway = $this->model->find($id);
        if ($gateway) {
            $this->model->where('active', 1)->update(['active' => 0]);
            $gateway->update(['active' => 1]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), SMSGatewayResource::make($gateway));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
