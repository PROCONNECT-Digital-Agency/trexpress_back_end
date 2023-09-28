<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WalletHistory\StatusChangeRequest;
use App\Http\Resources\WalletHistoryResource;
use App\Repositories\WalletRepository\WalletHistoryRepository;
use App\Services\WalletHistoryService\WalletHistoryService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WalletHistoryController extends AdminBaseController
{
    private WalletHistoryService $walletHistoryService;
    private WalletHistoryRepository $walletHistoryRepository;

    public function __construct(WalletHistoryService $walletHistoryService, WalletHistoryRepository $walletHistoryRepository)
    {
        parent::__construct();
        $this->walletHistoryService = $walletHistoryService;
        $this->walletHistoryRepository = $walletHistoryRepository;
    }

    public function paginate(Request $request)
    {
        $walletHistory = $this->walletHistoryRepository->walletHistoryPaginate($request->perPage ?? 15, $request->all());
        return WalletHistoryResource::collection($walletHistory);
    }

    public function changeStatus(string $uuid,StatusChangeRequest  $request)
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
}
