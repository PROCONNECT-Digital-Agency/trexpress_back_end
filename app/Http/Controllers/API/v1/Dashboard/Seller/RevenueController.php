<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Repositories\Interfaces\RevenueReportRepoInterface;
use App\Http\Requests\{FilterParamsRequest, ReportChartRequest};

class RevenueController extends SellerBaseController
{
    private RevenueReportRepoInterface $repository;


    public function __construct(
        RevenueReportRepoInterface $repository
    ) {
        parent::__construct();
        $this->repository = $repository;
    }

    public function reportChart(ReportChartRequest $request)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->repository->reportChartCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function reportPaginate(ReportChartRequest $request, FilterParamsRequest $filterParamsRequest)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->repository->reportPaginate($filterParamsRequest->get('perPage', 15));

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }
}
