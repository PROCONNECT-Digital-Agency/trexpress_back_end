<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use App\Repositories\BannerRepository\BannerRepository;
use App\Services\BannerService\BannerService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BannerController extends AdminBaseController
{
    private BannerRepository $bannerRepository;
    private Banner $model;
    private BannerService $bannerService;

    public function __construct(BannerRepository $bannerRepository, Banner $model, BannerService $bannerService)
    {
        parent::__construct();
        $this->bannerRepository = $bannerRepository;
        $this->model = $model;
        $this->bannerService = $bannerService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request)
    {
        $banners = $this->bannerRepository->bannersPaginate($request->perPage ?? 15, null, 'banner');
        return BannerResource::collection($banners);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $result = $this->bannerService->create($request->merge(['type' => 'banner']));
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), BannerResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang ?? config('app.locale')),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $banner = $this->bannerRepository->bannerDetails($id);
        if ($banner){
            return $this->successResponse(__('web.banner_found'), BannerResource::make($banner));
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
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $result = $this->bannerService->update($id, $request->merge(['type' => 'banner']) );
        if ($result['status']) {
            return $this->successResponse(trans('web.record_successfully_updated', [], \request()->lang), BannerResource::make($result['data']));
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
    public function destroy($id)
    {
        $banner = $this->bannerRepository->bannerDetails($id);
        if ($banner){
            $banner->delete();
            return $this->successResponse(__('web.record_successfully_deleted'), []);
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function setActiveBanner(int $id)
    {
        $banner = $this->model->find($id);
        if ($banner) {
            $banner->update(['active' => !$banner->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), BannerResource::make($banner));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
