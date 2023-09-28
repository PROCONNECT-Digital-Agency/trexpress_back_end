<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use App\Http\Requests\User\PointDelivery\IndexRequest;
use App\Http\Resources\PointDeliveryResource;
use App\Repositories\PointDeliveryRepository\PointDeliveryRepository;
use App\Services\PointDeliveryService\PointDeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class PointDeliveryController extends UserBaseController
{
    public function __construct(
        protected PointDeliveryService    $service,
        protected PointDeliveryRepository $repository
    )
    {
        parent::__construct();
    }

    /**
     * @param IndexRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        $collection = $request->validated();

        $products = $this->repository->paginate($collection);

        return PointDeliveryResource::collection($products);
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function show(int $id): JsonResponse|AnonymousResourceCollection
    {
        $result = $this->repository->show($id);

        if ($result) {
            return $this->successResponse(__('web.payment_found'), PointDeliveryResource::make($result));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
