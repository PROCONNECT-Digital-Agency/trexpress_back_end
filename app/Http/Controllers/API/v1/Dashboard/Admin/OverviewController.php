<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Repositories\Interfaces\OverviewReportRepoInterface;
use App\Http\Requests\{
    OverviewLeaderboardsReportRequest,
    OverviewReportChartRequest};

class OverviewController extends AdminBaseController
{
    private OverviewReportRepoInterface $repository;


    public function __construct(
        OverviewReportRepoInterface $repository
    ) {
        parent::__construct();
        $this->repository = $repository;
    }

    public function reportChart()
    {
        try {
            $result = $this->repository->reportChartCache();
            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }


    public function leaderboards(int $limit)
    {
        try {
            $result = $this->repository->leaderboards($limit);

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }
}
