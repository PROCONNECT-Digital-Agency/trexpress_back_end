<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use App\Repositories\UnitRepository\UnitRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnitController extends AdminBaseController
{
    private Unit $model;
    private UnitRepository $unitRepository;

    /**
     * @param Unit $model
     * @param UnitRepository $unitRepository
     */
    public function __construct(Unit $model, UnitRepository $unitRepository)
    {
        parent::__construct();
        $this->model = $model;
        $this->unitRepository = $unitRepository;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        $units = $this->unitRepository->unitsPaginate($request->perPage ?? 15, $request->active ?? null, $request->all());
        return UnitResource::collection($units);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $unit = $this->model->create([
                'active' => $request->active ?? 0,
                'position' => $request->position ?? 'after',
            ]);
            if ($unit){
                $unit->translations()->delete();

                foreach ($request->title as $index => $value){
                    $unit->translation()->create([
                        'locale' => $index,
                        'title' => $value,
                    ]);
                }
            }

            return $this->successResponse(__('web.record_successfully_created'), UnitResource::make($unit));

        } catch (\Exception $exception) {
            return $this->errorResponse(
                ResponseError::ERROR_400, $exception->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $unit = $this->unitRepository->unitDetails($id);
        if ($unit){
            return $this->successResponse(__('web.unit_found'), UnitResource::make($unit->load('translations')));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
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
        $unit = $this->unitRepository->unitDetails($id);
        if ($unit){
            try {
                $result = $unit->update([
                    'active' => $request->active ?? 0,
                    'position' => $request->position ?? 'after',
                ]);

                if ($result){
                    $unit->translations()->delete();
                    foreach ($request->title as $index => $value){
                        $unit->translation()->create([
                            'locale' => $index,
                            'title' => $value,
                        ]);
                    }
                }
                return $this->successResponse(__('web.record_successfully_created'), UnitResource::make($unit));
            } catch (\Exception $exception) {
                return $this->errorResponse(
                    ResponseError::ERROR_400, $exception->getMessage(),
                    Response::HTTP_BAD_REQUEST
                );
            }
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function setActiveUnit($id)
    {
        $unit = $this->model->find($id);
        if ($unit) {
            $unit->update(['active' => !$unit->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), UnitResource::make($unit));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
