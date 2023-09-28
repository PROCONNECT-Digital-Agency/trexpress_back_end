<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\DeliveryCreateRequest;
use App\Http\Resources\CurrencyResource;
use App\Http\Resources\DeliveryResource;
use App\Models\Delivery;
use App\Repositories\DeliveryRepository\DeliveryRepository;
use App\Services\DeliveryService\DeliveryService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function Aws\filter;

class DeliveryController extends AdminBaseController
{
    private DeliveryRepository $deliveryRepository;
    private DeliveryService $deliveryService;

    /**
     * @param DeliveryRepository $deliveryRepository
     * @param DeliveryService $deliveryService
     */
    public function __construct(DeliveryRepository $deliveryRepository, DeliveryService $deliveryService)
    {
        parent::__construct();
        $this->deliveryRepository = $deliveryRepository;
        $this->deliveryService = $deliveryService;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $deliveries = $this->deliveryRepository->deliveriesList($request->shop_id, $request->active, $request->all());
        return $this->successResponse(trans('web.deliveries_list', [], \request()->lang), DeliveryResource::collection($deliveries));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param DeliveryCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DeliveryCreateRequest $request)
    {
        $result = $this->deliveryService->create($request);
        if ($result['status']) {
            return $this->successResponse(trans('web.record_successfully_created', [], $request->lang), DeliveryResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $delivery = $this->deliveryRepository->deliveryDetails($id);

        if ($delivery) {
            $delivery->load('translations')->makeHidden('translation');

            return $this->successResponse(trans('web.delivery_found', [], \request()->lang), DeliveryResource::make($delivery));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DeliveryCreateRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(DeliveryCreateRequest $request, int $id)
    {
        $result = $this->deliveryService->update($id, $request);
        if ($result['status']) {
            return $this->successResponse(trans('web.record_successfully_updated', [], \request()->lang), DeliveryResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $delivery = Delivery::with(['orders'])->find($id);
        if ($delivery){
            $orderCount = $delivery->orders?->count();
            if ($orderCount > 0){
                return $this->errorResponse(
                    ResponseError::ERROR_507, trans('errors.' . ResponseError::ERROR_507, [], request()->lang),
                    Response::HTTP_BAD_REQUEST
                );
            }
            $delivery->delete();
            return $this->successResponse(trans('web.record_successfully_deleted', [], \request()->lang));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Get Delivery types.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function deliveryTypes()
    {
        return $this->successResponse(trans('web.delivery_types_list', [], \request()->lang), Delivery::TYPES);
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function setActive(int $id)
    {
        $delivery = $this->deliveryRepository->deliveryDetails($id);
        if ($delivery) {
            $delivery->update(['active' => !$delivery->active]);

            return $this->successResponse(__('web.record_has_been_successfully_updated'), DeliveryResource::make($delivery));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
