<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\Admin\ParcelOption\UpdateRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\Admin\ParcelOption\StoreRequest;
use App\Http\Resources\ParcelOptionResource;
use App\Models\ParcelOption;
use App\Repositories\ParcelOptionRepository\ParcelOptionRepository;
use App\Services\ParcelOptionService\ParcelOptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ParcelOptionController extends AdminBaseController
{
    public function __construct(
        private ParcelOptionRepository $repository,
        private ParcelOptionService $service
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

        return ParcelOptionResource::collection($orders);
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

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ParcelOptionResource::make(data_get($result, 'data')),
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
        $parcelOption = $this->repository->show($id);
        if ($parcelOption){
            return $this->successResponse(
                __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
                ParcelOptionResource::make($parcelOption)
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
     * @param ParcelOption $parcelOption
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(ParcelOption $parcelOption, UpdateRequest $request): JsonResponse
    {
        $result = $this->service->update($parcelOption, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ParcelOptionResource::make(data_get($result, 'data')),
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
}
