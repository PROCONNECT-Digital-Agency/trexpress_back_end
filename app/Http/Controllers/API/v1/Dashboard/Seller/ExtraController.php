<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\ExtraGroupResource;
use App\Http\Resources\ExtraValueResource;
use App\Models\ExtraGroup;
use App\Models\ExtraValue;
use App\Models\Language;
use App\Repositories\ExtraRepository\ExtraGroupRepository;
use App\Repositories\ExtraRepository\ExtraValueRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ExtraController extends SellerBaseController
{
    private ExtraGroupRepository $extraGroup;
    private ExtraValueRepository $extraValue;

    public function __construct(ExtraGroupRepository $extraGroup, ExtraValueRepository $extraValue)
    {
        parent::__construct();
        $this->extraGroup = $extraGroup;
        $this->extraValue = $extraValue;
    }

    public function extraGroupList(FilterParamsRequest $request)
    {
        $extraGroups = $this->extraGroup->extraGroupList(true, $request->all());
        return $this->successResponse(__('web.extra_group_list'), ExtraGroupResource::collection($extraGroups));
    }

    public function extraGroupDetails(int $id)
    {
        $extra = $this->extraGroup->extraGroupDetails($id);
        if ($extra) {
            return $this->successResponse(trans('web.extra_found', [], \request()->lang), ExtraGroupResource::make($extra));
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang ?? 'en'), Response::HTTP_NOT_FOUND);
    }

    public function extraValueList(int $groupId)
    {
        $extraValues = $this->extraValue->extraValueList(true, $groupId);
        return $this->successResponse(__('web.extra_values_list'), ExtraValueResource::collection($extraValues));
    }

    public function extraValueDetails(int $id)
    {
        $extraValue = $this->extraValue->extraValueDetails($id);
        if ($extraValue) {
            return $this->successResponse(__('web.extra_value_found'), ExtraValueResource::make($extraValue));
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang ?? 'en'), Response::HTTP_NOT_FOUND);
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function extraValue(Request $request)
    {
        $values = $this->extraValue->extraValueList(
            $request->active ?? null,
            $request->group_id ?? null,
            $request->perPage ?? 15,
            $request->search ?? null);

        return ExtraValueResource::collection($values);
    }
}
