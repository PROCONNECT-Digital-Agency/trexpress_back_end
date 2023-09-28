<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Resources\ExtraValueResource;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use App\Services\Interfaces\LanguageServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class LanguageController extends AdminBaseController
{
    private LanguageServiceInterface $languageService;
    private Language $model;

    public function __construct(LanguageServiceInterface $languageService, Language $model)
    {
        parent::__construct();
        $this->languageService = $languageService;
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $languages = $this->model->languagesList();
        return $this->successResponse(__('web.list_of_languages'), LanguageResource::collection($languages));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $result = $this->languageService->create($request);
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_create'), LanguageResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        $language = $this->model->find($id);
        if ($language) {
            return $this->successResponse(__('web.language_found'), LanguageResource::make($language));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
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
    public function update(Request $request, int $id)
    {
        $result = $this->languageService->update($id, $request);
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_create'), LanguageResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        $result = $this->languageService->delete($id);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Get Language where "default = 1".
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDefaultLanguage()
    {
        $language = $this->model->whereDefault(1)->first();
        if ($language) {
            return $this->successResponse(__('web.language_found'), LanguageResource::make($language));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Make specific Language as default
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function setDefaultLanguage($id)
    {
        $result = $this->languageService->setLanguageDefault($id, 1);
        if ($result['status']) {
            return $this->successResponse(__('web.item_is_default_now'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Get all Active languages
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveLanguages()
    {
        $languages = $this->model->whereActive(1)->get();
        return $this->successResponse(__('web.list_of_active_languages'), LanguageResource::collection($languages));
    }

    /**
     * Remove Model image from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function imageDelete(int $id)
    {
        $language = $this->model->find($id);
        if ($language) {
            Storage::disk('public')->delete($language->img);
            $language->update(['img' => null]);

            return $this->successResponse(__('web.image_has_been_successfully_delete'), $language);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function setActive(int $id)
    {
        $lang = $this->model->find($id);
        if ($lang) {
            $lang->update(['active' => !$lang->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), LanguageResource::make($lang));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

}
