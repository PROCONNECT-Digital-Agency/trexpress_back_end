<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\User\Notification\UserNotificationsRequest;
use App\Http\Requests\User\Profile\FireBaseTokenUpdateRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Resources\BannerResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\UserResource;
use App\Models\Banner;
use App\Models\Like;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\UserRepository\UserRepository;
use App\Services\UserServices\UserService;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends UserBaseController
{
    private  UserRepository $userRepository;
    private  UserService $userService;

    /**
     * @param UserRepository $userRepository
     * @param UserService $userService
     */
    public function __construct(UserRepository $userRepository, UserService $userService)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->userService = $userService;
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

        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), $request['data']);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $user = $this->userRepository->userById(auth('sanctum')->id());
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
     * @return JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function update(UserCreateRequest $request)
    {
        $result = $this->userService->update(auth('sanctum')->user()->uuid, $request);

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
     * @return JsonResponse
     */
    public function delete()
    {
        $user = $this->userRepository->userByUUID(auth('sanctum')->user()->uuid);
        if ($user) {
            $user->delete();
            return $this->successResponse(__('web.record_has_been_successfully_deleted'), []);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang ?? 'en'),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function fireBaseTokenUpdate(FireBaseTokenUpdateRequest $request)
    {
        $collection = $request->validated();
        if (empty($collection['firebase_token'])) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_502, 'message' => 'token is empty']);
        }

        $user = User::firstWhere('uuid', auth('sanctum')->user()->uuid);

        if (empty($user)) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        $tokens   = is_array($user->firebase_token) ? $user->firebase_token : [$user->firebase_token];
        $tokens[] = $collection['firebase_token'];

        $user->update([
            'firebase_token' => collect($tokens)->reject(fn($item) => empty($item))->unique()->values()->toArray()
        ]);

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language)
        );
    }

    public function passwordUpdate(PasswordUpdateRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $result = $this->userService->updatePassword($collection);
        if ($result['status']){
            return $this->successResponse(__('web.user_password_updated'), UserResource::make($result['data']));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, $result['message'] ?? trans('errors.' . ResponseError::ERROR_404, [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function likedLooks(FilterParamsRequest $request)
    {
        $user = $this->userRepository->userById(auth('sanctum')->id());
        if ($user) {
            $likes = Like::where(['likable_type' => Banner::class, 'user_id' => $user->id])->pluck('likable_id');
            $looks = Banner::whereIn('id', $likes)->paginate($request->perPaage ?? 15);

            return $this->successResponse(__('web.list_of_looks'), BannerResource::collection($looks));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    public function notificationsUpdate(UserNotificationsRequest $request): JsonResponse
    {
        $result = $this->userService->updateNotifications($request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            UserResource::make(data_get($result, 'data'))
        );
    }

    public function notifications(): AnonymousResourceCollection
    {
        return NotificationResource::collection($this->userRepository->usersNotifications());
    }

    public function notificationStatistic(): array
    {
        $notification = DB::table('push_notifications')
            ->select([
                DB::raw('count(id) as count'),
                DB::raw("sum(if(type = 'new_order', 1, 0)) as total_new_order_count"),
                DB::raw("sum(if(type = 'status_changed', 1, 0)) as total_status_changed_count"),
                DB::raw("sum(if(type = 'news_publish', 1, 0)) as total_news_publish_count"),
            ])
            ->whereNull('read_at')
            ->where('user_id', auth('sanctum')->id())
            ->first();

        $transaction = DB::table('transactions')
            ->select([
                DB::raw('count(id) as count'),

            ])
            ->where('status', Transaction::PROGRESS)
            ->where('user_id', auth('sanctum')->id())
            ->first();

        return [
            'notification'          => (int)$notification?->count,
            'new_order'             => (int)$notification?->total_new_order_count,
            'status_changed'        => (int)$notification?->total_status_changed_count,
            'news_publish'          => (int)$notification?->total_news_publish_count,
            'transaction'           => (int)$transaction?->count
        ];
    }
}
