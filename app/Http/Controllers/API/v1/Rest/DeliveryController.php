<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\Deliver\IndexRequest;
use App\Http\Resources\DeliveryResource;
use App\Repositories\DeliveryRepository\DeliveryRepository;
use App\Services\DeliveryService\DeliveryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class DeliveryController extends Controller
{
    use ApiResponse;
    protected DeliveryRepository $deliveryRepository;
    protected DeliveryService $deliveryService;

    public function __construct(DeliveryRepository $deliveryRepository, DeliveryService $deliveryService)
    {
        $this->deliveryRepository = $deliveryRepository;
        $this->deliveryService = $deliveryService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     * @return AnonymousResourceCollection
     */
    public function paginate(IndexRequest $request): AnonymousResourceCollection
    {
        $collection = $request->validated();
        $deliveries = $this->deliveryRepository->deliveriesListForOrder($collection);
        return DeliveryResource::collection($deliveries);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $delivery = $this->deliveryRepository->deliveryDetailOrder($id);
        if ($delivery) {
            return $this->successResponse(__('errors.' . ResponseError::NO_ERROR), DeliveryResource::make($delivery));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
