<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\WalletHistoryResource;
use App\Http\Resources\WalletResource;
use App\Models\User;
use App\Models\WalletHistory;
use App\Repositories\Interfaces\UserRepoInterface;
use App\Repositories\WalletRepository\WalletHistoryRepository;
use App\Services\AuthService\UserVerifyService;
use App\Services\Interfaces\UserServiceInterface;
use App\Services\UserServices\UserService;
use App\Services\UserServices\UserWalletService;
use App\Services\WalletHistoryService\WalletHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AdminBaseController
{
    private UserServiceInterface $userService;
    private UserRepoInterface $userRepository;

    /**
     * @param UserServiceInterface $userService
     * @param UserRepoInterface $userRepository
     */
    public function __construct(UserServiceInterface $userService, UserRepoInterface $userRepository)
    {
        parent::__construct();
        $this->userService = $userService;
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        $users = $this->userRepository->usersPaginate($request->perPage ?? 15, $request->all(), $request->active);
        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserCreateRequest $request
     * @return JsonResponse
     */
    public function store(UserCreateRequest $request)
    {
        $result = $this->userService->create($request);
        if ($result['status']){
            (new UserVerifyService())->verifyEmail($result['data']);

            return $this->successResponse(__('web.user_create'), UserResource::make($result['data']));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, $result['message'] ?? trans('errors.' . ResponseError::ERROR_404, [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param string $uuid
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function show(string $uuid)
    {
        $user = $this->userRepository->userByUUID($uuid);
        if ($user) {
            return $this->successResponse(__('web.user_found'), UserResource::make($user));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $uuid
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function update(Request $request, string $uuid)
    {
        $result = $this->userService->update($uuid, $request);
        if ($result['status']){
            return $this->successResponse(__('web.user_updated'), UserResource::make($result['data']));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, $result['message'] ?? trans('errors.' . ResponseError::ERROR_404, [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $uuid
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function destroy(string $uuid): AnonymousResourceCollection|JsonResponse
    {
        $user = User::where('uuid',$uuid)->first();
        if ($user){

            if ($user->role == 'seller'){
                if($user->shop){
                    return $this->errorResponse(
                        ResponseError::ERROR_434, trans('errors.' . ResponseError::ERROR_434, [], request()->lang),
                        Response::HTTP_NOT_FOUND
                    );
                }
                $user->delete();
            }
            if ($user->role == 'deliveryman'){
                if($user->deliverymanOrders->isNotEmpty()){
                    return $this->errorResponse(
                        ResponseError::ERROR_435, trans('errors.' . ResponseError::ERROR_435, [], request()->lang),
                        Response::HTTP_NOT_FOUND
                    );
                }
                $user->delete();
            }

            $user->delete();
            return $this->successResponse(__('web.user_deleted'));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    public function updateRole($uuid, Request $request){

        try {
            $user = $this->userRepository->userByUUID($uuid);
            if ($user){
                if (isset($user->shop) && $user->shop->status == 'approved' || $user->role == 'seller' || $request->role == 'seller') {
                    return $this->errorResponse(ResponseError::ERROR_110, __('errors.' . ResponseError::ERROR_110), Response::HTTP_BAD_REQUEST);
                }
                $user->syncRoles([$request->role]);
                return $this->successResponse(__('web.record_successfully_updated'), UserResource::make($user));
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $e){
            return $this->errorResponse(ResponseError::ERROR_400, $e->getMessage(),Response::HTTP_BAD_REQUEST);
        }
    }

    public function usersSearch(Request $request)
    {
        $users = $this->userRepository->usersSearch($request->search ?? '', true, $request->roles ?? []);
        return UserResource::collection($users);
    }

    public function setActive(string $uuid)
    {
        $user = $this->userRepository->userByUUID($uuid);
        if ($user) {
            $user->active = !$user->active;
            $user->save();

            return $this->successResponse(__('web.record_has_been_successfully_updated'), UserResource::make($user));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Top up User Wallet by UUID
     *
     * @param string $uuid
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function topUpWallet(string $uuid, FilterParamsRequest $request){
        $user = User::firstWhere('uuid', $uuid);
        if ($user) {
            $result = (new UserWalletService())->update($user, ['price' => $request->price, 'note' => $request->note]);
            if ($result['status']) {
                return $this->successResponse(__('web.walled_has_been_updated'), UserResource::make($user));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }


    /**
     * Get User Wallet History by UUID
     *
     * @param string $uuid
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function walletHistories(string $uuid, FilterParamsRequest $request)
    {
        $user = User::firstWhere('uuid', $uuid);
        if ($user) {
            $histories = (new WalletHistoryRepository)->walletHistoryPaginate($request->perPage ?? 15,
                ['wallet_uuid' => $user->wallet->uuid]);
            return WalletHistoryResource::collection($histories);
        }

        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }


    /**
     * Get User Wallet History by UUID
     *
     * @param string $uuid
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function walletClear(string $uuid): JsonResponse|AnonymousResourceCollection
    {
        $user = User::firstWhere('uuid', $uuid);
        if ($user) {
            if ($user->wallet())
            {
                WalletHistory::create([
                    'uuid' => Str::uuid(),
                    'wallet_uuid' => $user->walletHasOne->uuid,
                    'type' => 'withdraw',
                    'price' => $user->walletHasOne->price,
                    'note' => null,
                    'created_by' => auth('sanctum')->user()->id,
                    'status' => 'paid',
                ]);

                (new WalletHistoryService())->create($user,['price' => $user->walletHasOne->price]);

                $user->walletHasOne()->update(['price' => 0]);

                return $this->successResponse(__('web.wallet_has_been_updated'), []);
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
