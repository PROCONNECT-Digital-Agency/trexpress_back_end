<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\User\AddressStoreRequest;
use App\Http\Resources\UserAddressResource;
use App\Models\UserAddress;
use App\Services\UserServices\UserAddressService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAddressController extends AdminBaseController
{
    private UserAddressService $addressService;
    private UserAddress $model;

    /**
     * @param UserAddressService $addressService
     * @param UserAddress $model
     */
    public function __construct(UserAddressService $addressService, UserAddress $model)
    {
        parent::__construct();
        $this->addressService = $addressService;
        $this->model = $model;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function paginate()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param string $uuid
     * @param AddressStoreRequest $request
     * @return JsonResponse
     */
    public function store(string $uuid, AddressStoreRequest $request)
    {
        $user = User::firstWhere('uuid', $uuid);
        if ($user){
            $result = $this->addressService->create($request->merge(['user_id' => $user->id]));
            if ($result['status']){
                return $this->successResponse(__('web.user_create'), UserAddressResource::make($result['data']));
            }
            return $this->errorResponse(
                ResponseError::ERROR_400, $result['message'] ?? trans('errors.' . ResponseError::ERROR_400, [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang ?? 'en'),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
