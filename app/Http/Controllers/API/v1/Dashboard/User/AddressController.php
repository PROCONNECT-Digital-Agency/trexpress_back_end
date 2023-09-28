<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\User\AddressStoreRequest;
use App\Http\Requests\User\AddressUpdateRequest;
use App\Http\Requests\User\FindexAddressStoreRequest;
use App\Http\Requests\User\FindexAddressUpdateRequest;
use App\Http\Resources\UserAddressResource;
use App\Models\UserAddress;
use App\Services\UserServices\UserAddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class AddressController extends UserBaseController
{
    private UserAddress $model;
    private UserAddressService $addressService;

    public function __construct(UserAddress $model, UserAddressService $addressService)
    {
        parent::__construct();
        $this->addressService = $addressService;
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(FilterParamsRequest $request)
    {
        $address = $this->model->where('user_id', auth('sanctum')->id())->paginate($request->perPage ?? 15);
        return $this->successResponse(__('web.list_of_address'), UserAddressResource::collection($address));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AddressStoreRequest $request
     * @return JsonResponse
     */
    public function store(AddressStoreRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $collection['user_id'] = auth('sanctum')->user()->id;

        $result = $this->addressService->create($collection);

        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'), UserAddressResource::make($result['data']));
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
     * @return JsonResponse
     */
    public function show(int $id)
    {
        $address = $this->model->where(['user_id' => auth('sanctum')->id(), 'id' => $id])->first();
        if ($address) {
            return $this->successResponse(__('web.address_found'), UserAddressResource::make($address));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AddressUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(AddressUpdateRequest $request, int $id)
    {
        $collection = $request->validated();

        $collection['user_id'] = auth('sanctum')->user()->id;

        $result = $this->addressService->update($id, $collection);

        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'), UserAddressResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function destroy(int $id)
    {
        $result = $this->addressService->destroy($id);
        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_deleted'), []);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Make specific Language as default
     * @param int $id
     * @return JsonResponse
     */
    public function setDefaultAddress(int $id)
    {
        $result = $this->addressService->setAddressDefault($id, 1);
        if ($result['status']) {
            return $this->successResponse(__('web.item_is_default_now'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function setActiveAddress(int $id)
    {
        $address = $this->model->where(['user_id' => auth('sanctum')->id(), 'id' => $id])->first();
        if ($address) {
            $address->update(['active' => !$address->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), UserAddressResource::make($address));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
