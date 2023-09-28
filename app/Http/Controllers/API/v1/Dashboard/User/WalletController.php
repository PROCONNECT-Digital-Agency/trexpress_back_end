<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WalletHistory\StatusChangeRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\WalletHistoryResource;
use App\Models\PointHistory;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Repositories\UserRepository\UserRepository;
use App\Repositories\WalletRepository\WalletHistoryRepository;
use App\Services\WalletHistoryService\WalletHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WalletController extends UserBaseController
{
    private WalletHistoryRepository $walletHistoryRepository;
    private WalletHistoryService $walletHistoryService;

    /**
     * @param WalletHistoryRepository $walletHistoryRepository
     * @param WalletHistoryService $walletHistoryService
     */
    public function __construct(WalletHistoryRepository $walletHistoryRepository, WalletHistoryService $walletHistoryService)
    {
        parent::__construct();
        $this->walletHistoryRepository = $walletHistoryRepository;
        $this->walletHistoryService = $walletHistoryService;
    }

    public function walletHistories(FilterParamsRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $histories = $this->walletHistoryRepository->walletHistoryPaginate($request->perPage ?? 15, $request->merge([
            'wallet_uuid' => auth('sanctum')->user()->wallet->uuid])->all());
        return WalletHistoryResource::collection($histories);
    }

    public function store(Request $request)
    {
        $user = auth('sanctum')->user();

        if (!isset($user->wallet) || $user->wallet->price >= $request->price) {
            $result = $this->walletHistoryService->create(auth('sanctum')->user(), $request->merge([
                'status' => 'processed',
                'type' => 'withdraw',
            ]));

            if ($result['status']) {

                return $this->successResponse( __('web.record_was_successfully_create'), WalletHistoryResource::make($result['data']));
            }

            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->errorResponse(
            ResponseError::ERROR_109, trans('errors.' . ResponseError::ERROR_109, [], \request()->lang ?? config('app.locale')),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function changeStatus(string $uuid, StatusChangeRequest $request)
    {
        $collection = $request->validated();

        $result = $this->walletHistoryService->changeStatus($uuid, $collection['status']);
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_updated'), []);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function pointHistories(FilterParamsRequest $request)
    {
        $histories = PointHistory::where('user_id', auth('sanctum')->id())
            ->orderBy($request->column ?? 'created_at', $request->sort ?? 'desc')
            ->paginate($request->perPage ?? 15);

        return $histories;
    }
}
