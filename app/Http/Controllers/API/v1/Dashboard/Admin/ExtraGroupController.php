<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryResource;
use App\Http\Resources\ExtraGroupResource;
use App\Models\ExtraGroup;
use App\Models\Language;
use App\Repositories\ExtraRepository\ExtraGroupRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ExtraGroupController extends AdminBaseController
{
    private ExtraGroupRepository $groupRepository;
    private ExtraGroup $model;

    public function __construct(ExtraGroup $model, ExtraGroupRepository $groupRepository)
    {
        parent::__construct();
        $this->model = $model;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $extras = $this->groupRepository->extraGroupList($request->active ?? null, $request->all());
        return ExtraGroupResource::collection($extras);
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
        $extra = $this->model->create($request->all());
        if ($extra && isset($request->title)) {
            foreach ($request->title as $index => $title) {
                $extra->translation()->create([
                    'locale' => $index,
                    'title' => $title,
                ]);
            }
            return $this->successResponse(trans('web.extras_list', [], \request()->lang), $extra);
        }
        return $this->errorResponse(trans('web.extras_list', [], \request()->lang), $extra);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $extra = $this->groupRepository->extraGroupDetails($id);
        if ($extra) {
            return $this->successResponse(trans('web.extra_found', [], \request()->lang), ExtraGroupResource::make($extra->load('translations')));
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang), Response::HTTP_NOT_FOUND);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $extra = $this->model->find($id);
        if ($extra) {
            $extra->update($request->all());
            if (isset($request->title)) {
                $extra->translations()->delete();
                foreach ($request->title as $index => $title) {
                    $extra->translation()->create([
                        'locale' => $index,
                        'title' => $title,
                    ]);
                }
                return $this->successResponse(trans('web.record_has_been_successfully_updated', [], \request()->lang), ExtraGroupResource::make($extra));
            }
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
        $group = $this->model->find($id);
        if ($group) {
            if (count($group->extraValues) > 0){
                return $this->errorResponse(ResponseError::ERROR_504, trans('errors.' . ResponseError::ERROR_504, [], \request()->lang), Response::HTTP_BAD_REQUEST);
            }
            $group->delete();
            return $this->successResponse(trans('web.record_has_been_successfully_deleted', [], \request()->lang), []);
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang), Response::HTTP_NOT_FOUND);
    }


    /**
     * ExtraGroup type list.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function typesList() {
        return $this->successResponse('web.extra_groups_types', $this->model->getTypes());
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function setActive(int $id)
    {
        $group = $this->groupRepository->extraGroupDetails($id);
        if ($group) {
            $group->update(['active' => !$group->active]);

            return $this->successResponse(__('web.record_has_been_successfully_updated'), ExtraGroupResource::make($group));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
