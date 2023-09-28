<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\FAQResource;
use App\Models\Faq;
use App\Models\PrivacyPolicy;
use App\Models\TermCondition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class FAQController extends RestBaseController
{
    private Faq $model;

    public function __construct(Faq $model)
    {
        $this->model = $model;
        $this->lang = request('lang') ?? null;

    }

    /**
     * Display a listing of the FAQ.
     *
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $faqs = $this->model->with([
            'translation' => fn($q) => $q->where('locale', $request->lang ?? 'en')
        ])
            ->where('active', 1)
            ->orderBy($request->column ?? 'id', $request->sort ?? 'desc')
            ->paginate($request->perPage ?? 15);

        return FAQResource::collection($faqs);
    }

    /**
     * Display Terms & Condition.
     *
     * @return JsonResponse
     */
    public function term(): JsonResponse
    {
        $model = TermCondition::with(['translation' => fn($q) => $q->where('locale', $this->lang ?? 'en')])->first();
        if ($model){
            return $this->successResponse(__('web.model_found'), $model);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang ?? 'en'),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Display Terms & Condition.
     *
     * @return JsonResponse
     */
    public function policy(): JsonResponse
    {
        $model = PrivacyPolicy::with(['translation' => fn($q) => $q->where('locale', $this->lang ?? 'en')])->first();
        if ($model){
            return $this->successResponse(__('web.model_found'), $model);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang ?? 'en'),
            Response::HTTP_NOT_FOUND
        );
    }

}
