<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Http\Requests\Admin\DeliveryManSetting\StoreRequest;
use App\Http\Requests\Admin\DeliveryManSetting\UpdateRequest;
use App\Models\User;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Models\DeliveryManSetting;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\DeliveryManSettingResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Services\DeliveryManSettingServices\DeliveryManSettingService;
use App\Repositories\DeliveryManSettingRepository\DeliveryManSettingRepository;

class DeliveryManSettingController extends AdminBaseController
{

    public function __construct(protected DeliveryManSettingRepository $repository,protected DeliveryManSettingService $service)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $filter = $request->all();

        $deliveryMans = $this->repository->paginate($filter);

        return DeliveryManSettingResource::collection($deliveryMans);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $deliveryMan = User::find(data_get($validated, 'user_id'));

        if (!$deliveryMan->hasRole('deliveryman')) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_400, 'message' => 'You need change delivery man']);
        }

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('web.record_successfully_created'),
            DeliveryManSettingResource::make(data_get($result, 'data'))
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return $this->successResponse(
            __('web.delivery_man_setting_found'),
            DeliveryManSettingResource::make($this->repository->detail($id))
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(int $id, UpdateRequest $request): JsonResponse
    {
        $deliveryManSetting = DeliveryManSetting::find($id);

        if (empty($deliveryManSetting)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $validated = $request->validated();

        $deliveryMan = User::find(data_get($validated, 'user_id'));

        if (!$deliveryMan->hasRole('deliveryman')) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_400, 'message' => 'You need change delivery man']);
        }

        $result = $this->service->update($deliveryManSetting, $validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('web.record_has_been_successfully_updated'),
            DeliveryManSettingResource::make(data_get($result, 'data'))
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAllRequest $request
     * @return JsonResponse
     */
    public function destroy(DeleteAllRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $this->service->delete($collection['ids']);

        return $this->successResponse(__('web.record_successfully_deleted'), []);
    }
}
