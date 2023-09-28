<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\TransactionResource;
use App\Repositories\TransactionRepository\TransactionRepository;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends AdminBaseController
{
    private TransactionRepository $transactionRepository;

    /**
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(TransactionRepository $transactionRepository)
    {
        parent::__construct();
        $this->transactionRepository = $transactionRepository;
    }

    public function paginate(FilterParamsRequest $request)
    {
        $transactions = $this->transactionRepository->paginate($request->perPage ?? 15, $request->all());
        return TransactionResource::collection($transactions);
    }

    public function show(int $id)
    {
        $transaction = $this->transactionRepository->show($id);
        if ($transaction) {
            return $this->successResponse(ResponseError::NO_ERROR, TransactionResource::make($transaction));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
