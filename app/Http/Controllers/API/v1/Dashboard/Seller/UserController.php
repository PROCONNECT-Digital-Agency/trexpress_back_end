<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserAddressResource;
use App\Http\Resources\UserResource;
use App\Models\Invitation;
use App\Models\User;
use App\Repositories\UserRepository\UserRepository;
use App\Services\AuthService\UserVerifyService;
use App\Services\UserServices\UserAddressService;
use App\Services\UserServices\UserService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends SellerBaseController
{
    private UserRepository $userRepository;
    private UserService $userService;
    private User $model;

    public function __construct(User $model, UserRepository $userRepository, UserService $userService)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->model = $model;
    }

    public function paginate(Request $request)
    {
        if ($this->shop) {
            $users = $this->userRepository->usersPaginate($request->perPage ?? 15, $request->merge(['role' => 'user'])->all(), true);
            return UserResource::collection($users);
        }  else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function show(string $uuid)
    {
        if ($this->shop) {
            $user = $this->userRepository->userByUUID($uuid);
            if ($user) {
                return $this->successResponse(__('web.user_found'), UserResource::make($user));
            }
            return $this->errorResponse(
                ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND
            );
        }  else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserCreateRequest $request)
    {
        if ($this->shop) {
            $result = $this->userService->create($request->merge(['role' => 'user']));
        if ($result['status']){
            (new UserVerifyService())->verifyEmail($result['data']);
            return $this->successResponse(__('web.user_create'), UserResource::make($result['data']));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, $result['message'] ?? trans('errors.' . ResponseError::ERROR_404, [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
        }  else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function userAddressCreate($uuid, Request $request)
    {
        if ($this->shop) {
            $user = $this->userRepository->userByUUID($uuid);
            if ($user) {
                $result = (new UserAddressService)->create($request->merge(['user_id' => $user->id]));
                if ($result['status']){
                    return $this->successResponse(__('web.user_address_create'), UserAddressResource::make($result['data']));
                }

                return $this->errorResponse(
                    ResponseError::ERROR_404, $result['message'] ?? trans('errors.' . ResponseError::ERROR_404, [], \request()->lang),
                    Response::HTTP_BAD_REQUEST
                );
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND
            );
        }  else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function shopUsersPaginate(FilterParamsRequest $request)
    {
        $search = $request->search ?? null;
        if ($this->shop) {
            $users = $this->model->with('roles')
                ->whereHas('invite', function ($q) {
                    $q->where(['shop_id' => $this->shop->id, 'status' => Invitation::STATUS['excepted']]);
                })
                ->when(isset($request->search), function ($q) use($search) {
                    $q->where(function($query) use ($search) {
                        $query->where('firstname', 'LIKE', '%'. $search . '%')
                            ->orWhere('lastname', 'LIKE', '%'. $search . '%')
                            ->orWhere('email', 'LIKE', '%'. $search . '%')
                            ->orWhere('phone', 'LIKE', '%'. $search . '%');
                    });
                })
                ->when(isset($request->role), function ($q) use($request) {
                    $q->whereHas('roles', function ($q) use($request){
                        $q->where('name', $request->role);
                    });
                })
                ->when(isset($request->active), function ($q) use($request) {
                    $q->where('active', $request->active);
                })
                ->orderBy($request->column ?? 'id', $request->sort ?? 'desc')
                ->paginate($request->perPage ?? 15);

            return UserResource::collection($users);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function shopUserShow(string $uuid)
    {
        if ($this->shop) {
            $user = $this->userRepository->userByUUID($uuid);
            if ($user && optional($user->invite)->shop_id == $this->shop->id) {
                return $this->successResponse(__('web.user_found'), UserResource::make($user));
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function getDeliveryman(FilterParamsRequest $request)
    {
        $search = $request->search ?? null;
        if ($this->shop) {
            $users = $this->model->with('roles')
                ->whereHas('roles', function ($q) use($request){
                    $q->where('name', 'deliveryman');
                })
                ->whereDoesntHave('invite', function ($q){
                    $q->where('shop_id', '!=', $this->shop->id)
                    ->where('status', 3);
                })
                ->when(isset($request->search), function ($q) use($search) {
                    $q->where(function($query) use ($search) {
                        $query->where('firstname', 'LIKE', '%'. $search . '%')
                            ->orWhere('lastname', 'LIKE', '%'. $search . '%')
                            ->orWhere('email', 'LIKE', '%'. $search . '%')
                            ->orWhere('phone', 'LIKE', '%'. $search . '%');
                    });
                })
                ->whereActive(1)
                ->orderBy($request->column ?? 'id', $request->sort ?? 'desc')
                ->paginate($request->perPage ?? 15);

            return UserResource::collection($users);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function setUserActive($uuid)
    {
        if ($this->shop) {
            $user = $this->userRepository->userByUUID($uuid);
            if ($user && optional($user->invite)->shop_id == $this->shop->id) {
                $user->update(['active' => !$user->active]);

                return $this->successResponse(__('web.user_found'), UserResource::make($user));
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }
}
