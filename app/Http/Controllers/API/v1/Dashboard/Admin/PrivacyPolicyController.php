<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Models\PrivacyPolicy;
use App\Services\PrivacyPolicyService\PrivacyPolicyService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrivacyPolicyController extends AdminBaseController
{
    private PrivacyPolicy $model;

    /**
     * @param PrivacyPolicy $model
     */
    public function __construct(PrivacyPolicy $model,PrivacyPolicyService $service)
    {
        parent::__construct();
        $this->model = $model;
        $this->service = $service;
        $this->lang = request('lang') ?? null;

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param $type
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->model::query()->delete();
        $condition = $this->service->create($request);
        if ($condition['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_created'), $condition['data']);
        }
        return $this->errorResponse(
            ResponseError::ERROR_501, trans('errors.' . ResponseError::ERROR_501, [], \request()->lang ?? 'en'),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        $model = $this->model->with(['translation' => fn($q) => $q->where('locale', $this->lang),'translations'])->first();
        if ($model){
            return $this->successResponse(__('web.model_found'), $model);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang ?? 'en'),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $term = $this->service->update($id,$request);
        if ($term['status']){
            return $this->successResponse(__('web.record_has_been_successfully_updated'), $term);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang ?? 'en'),
            Response::HTTP_NOT_FOUND
        );
    }
}
