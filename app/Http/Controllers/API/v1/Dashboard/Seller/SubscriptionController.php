<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\ShopSubscription;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Services\SubscriptionService\SubscriptionService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends SellerBaseController
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
        if ($this->shop) {
            $subscriptions = $this->model->subscriptionsList()->where('active', 1);
            return $this->successResponse(trans('web.subscription_list', [], \request()->lang ?? config('app.locale')), SubscriptionResource::collection($subscriptions));
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function subscriptionAttach(int $id)
    {
        if ($this->shop) {
            $subscription = Subscription::find($id);
            if ($subscription) {
                $shopSubscription = ShopSubscription::create([
                    'shop_id' => $this->shop->id,
                    'subscription_id' => $subscription->id,
                    'expired_at' => now()->addMonths($subscription->month),
                    'price' => $subscription->price,
                    'type' => $subscription->type ?? 'order',
                    'active' => 0,
                ]);
                return $this->successResponse(trans('web.subscription_attached', [], \request()->lang ?? config('app.locale')), $shopSubscription);
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_NOT_FOUND
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_FORBIDDEN
            );
        }
    }

}
