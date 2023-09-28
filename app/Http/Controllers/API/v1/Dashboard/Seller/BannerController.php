<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use App\Repositories\BannerRepository\BannerRepository;
use App\Services\BannerService\BannerService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BannerController extends SellerBaseController
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function paginate(FilterParamsRequest $request)
    {
        if ($this->shop) {
            $banners = $this->bannerRepository->bannersPaginate($request->perPage ?? 15, null, 'look', $this->shop->id);
            return BannerResource::collection($banners);
        }  else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($this->shop) {
            $result = $this->bannerService->create($request->merge(['type' => 'look', 'shop_id' => $this->shop->id]));
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), BannerResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang ?? config('app.locale')),
            Response::HTTP_BAD_REQUEST
        );
        }  else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
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
        if ($this->shop) {
            $banner = $this->bannerRepository->bannerDetails($id);
            if ($banner){
                return $this->successResponse(__('web.banner_found'), BannerResource::make($banner->load('translations')));
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND
            );
        }  else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
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
        if ($this->shop) {
            $result = $this->bannerService->update($id, $request->merge(['type' => 'look', 'shop_id' => $this->shop->id]));
            if ($result['status']) {
                return $this->successResponse(trans('web.record_successfully_updated', [], \request()->lang), BannerResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        }  else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if ($this->shop) {
            $banner = $this->bannerRepository->bannerDetails($id);
            if ($banner){
                $banner->delete();
                return $this->successResponse(__('web.record_successfully_deleted'), []);
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND
            );
        }  else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function setActiveBanner(int $id)
    {
        if ($this->shop) {
            $banner = $this->model->find($id);
            if ($banner) {
                $banner->update(['active' => !$banner->active]);
                return $this->successResponse(__('web.record_has_been_successfully_updated'), BannerResource::make($banner));
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND
            );
        }  else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }
}
