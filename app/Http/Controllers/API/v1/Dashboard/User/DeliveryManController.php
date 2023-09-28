<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\UserServices\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeliveryManController extends UserBaseController
{

    public function __construct(protected UserService $service)
    {
        parent::__construct();
    }

    /**
     * Add Review to OrderDetails.
     *
     * @param int $orderId
     * @param Request $request
     * @return JsonResponse
     */
    public function addReviewToDeliveryMan(int $orderId, Request $request): JsonResponse
    {
        $result = $this->service->addReviewToDeliveryMan($orderId, $request);
        if ($result['status']) {
            return $this->successResponse(ResponseError::NO_ERROR, UserResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }
}
