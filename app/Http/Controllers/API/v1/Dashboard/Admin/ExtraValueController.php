<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExtraGroupResource;
use App\Http\Resources\ExtraValueResource;
use App\Models\ExtraValue;
use App\Models\Language;
use App\Repositories\ExtraRepository\ExtraGroupRepository;
use App\Repositories\ExtraRepository\ExtraValueRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ExtraValueController extends AdminBaseController
{
    private $model, $valueRepository;

    /**
     * @param ExtraValue $model
     * @param ExtraValueRepository $valueRepository
     */
    public function __construct(ExtraValue $model, ExtraValueRepository $valueRepository)
    {
        parent::__construct();
        $this->model = $model;
        $this->valueRepository = $valueRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $values = $this->valueRepository->extraValueList(
            $request->active ?? null,
            $request->group_id ?? null,
            $request->perPage ?? 15,
            $request->search ?? null);

        return ExtraValueResource::collection($values);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(Request $request)
    {
        try {
            $group = (new ExtraGroupRepository())->extraGroupDetails($request->extra_group_id);
            if ($group) {
                $value = $group->extraValues()->create($request->all());
                if (isset($request->images)) {
                    $value->uploads($request->images);
                }
                return $this->successResponse(trans('web.record_has_been_successfully_created', [], \request()->lang), ExtraValueResource::make($value));
            }
            return $this->errorResponse(ResponseError::ERROR_404, trans('web.extra_group_not_found', [], \request()->lang), Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->errorResponse(ResponseError::ERROR_400, $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $extraValue = $this->valueRepository->extraValueDetails($id);
        if ($extraValue) {
            return $this->successResponse(trans('web.extra_value_found', [], \request()->lang), ExtraValueResource::make($extraValue));
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang), Response::HTTP_NOT_FOUND);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update(Request $request, $id)
    {
        $value = $this->model->find($id);
        if ($value) {
            $value->update($request->all());
            return $this->successResponse(trans('web.record_has_been_successfully_updated', [], \request()->lang),  ExtraValueResource::make($value));
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang), Response::HTTP_NOT_FOUND);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $value = $this->model->find($id);
        if ($value) {
            if (count($value->stocks) > 0){
                return $this->errorResponse(ResponseError::ERROR_504, trans('errors.' . ResponseError::ERROR_504, [], \request()->lang), Response::HTTP_BAD_REQUEST);
            }
            $value->delete();
            return $this->successResponse(trans('web.record_has_been_successfully_deleted', [], \request()->lang), []);
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang), Response::HTTP_NOT_FOUND);
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function setActive(int $id)
    {
        $value = $this->model->find($id);
        if ($value) {
            $value->update(['active' => !$value->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), ExtraValueResource::make($value));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

}
