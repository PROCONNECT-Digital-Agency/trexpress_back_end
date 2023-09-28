<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\Admin\ParcelOrderSetting\StoreRequest;
use App\Http\Resources\ParcelOrderSettingResource;
use App\Models\ParcelOrderSetting;
use App\Repositories\ParcelOrderSettingRepository\ParcelOrderSettingRepository;
use App\Services\ParcelOrderSettingService\ParcelOrderSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class ParcelOrderSettingController extends AdminBaseController
{
    public function __construct(
        private ParcelOrderSettingRepository $repository,
        private ParcelOrderSettingService $service
    )
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $orders = $this->repository->paginate($request->all());

        return ParcelOrderSettingResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ParcelOrderSettingResource::make($this->repository->show(data_get($result, 'data'))),
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
        $parcelOrderSetting = $this->repository->show($id);
        if ($parcelOrderSetting){
            return $this->successResponse(
                __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
                ParcelOrderSettingResource::make($parcelOrderSetting)
            );
        }
        return $this->onErrorResponse([
            'code'      => ResponseError::ERROR_404,
            'message'   => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ParcelOrderSetting $parcelOrderSetting
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function update(ParcelOrderSetting $parcelOrderSetting, StoreRequest $request): JsonResponse
    {
        $result = $this->service->update($parcelOrderSetting, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ParcelOrderSettingResource::make($this->repository->show(data_get($result, 'data'))),
        );
    }

    /**
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function destroy(FilterParamsRequest $request): JsonResponse
    {
        $result = $this->service->destroy($request->input('ids'));

        if (count($result) > 0) {

            return $this->onErrorResponse([
                'code'      => ResponseError::ERROR_400,
                'message'   => __('errors.' . ResponseError::ERROR_400, [
                    'ids' => implode(', #', $result)
                ], locale: $this->language)
            ]);

        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language)
        );
    }

//    /**
//     * @return JsonResponse
//     */
//    public function dropAll(): JsonResponse
//    {
//        $this->service->dropAll();
//
//        return $this->successResponse(
//            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language)
//        );
//    }
//
//    /**
//     * @return JsonResponse
//     */
//    public function truncate(): JsonResponse
//    {
//        $this->service->truncate();
//
//        return $this->successResponse(
//            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language)
//        );
//    }
//
//    /**
//     * @return JsonResponse
//     */
//    public function restoreAll(): JsonResponse
//    {
//        $this->service->restoreAll();
//
//        return $this->successResponse(
//            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language)
//        );
//    }
}
