<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Services\SubscriptionService\SubscriptionService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends AdminBaseController
{
    private Subscription $model;
    private SubscriptionService $subscriptionService;

    /**
     * @param Subscription $model
     * @param SubscriptionService $subscriptionService
     */
    public function __construct(Subscription $model, SubscriptionService $subscriptionService)
    {
        parent::__construct();
        $this->model = $model;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $subscriptions = $this->model->subscriptionsList();
        return $this->successResponse(trans('web.subscription_list', [], \request()->lang), SubscriptionResource::collection($subscriptions));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $subscription = $this->model->find($id);
        if ($subscription) {
            return $this->successResponse(trans('web.subscription_list', [], \request()->lang), SubscriptionResource::make($subscription));
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.ERROR_404', [], \request()->lang), Response::HTTP_NOT_FOUND);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $result = $this->subscriptionService->update($id, $request);
        if ($result['status']) {

            cache()->forget('subscriptions-list');
            return $this->successResponse(trans('web.subscription_list', [], \request()->lang), SubscriptionResource::make($result['data']));
        }
        return $this->errorResponse($result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
