<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\BannerResource;
use App\Http\Resources\ProductResource;
use App\Models\Banner;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Repositories\BannerRepository\BannerRepository;
use App\Repositories\ProductRepository\ProductRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BannerController extends RestBaseController
{
    private BannerRepository $bannerRepository;

    /**
     * @param BannerRepository $bannerRepository
     * @param Banner $model
     */
    public function __construct(BannerRepository $bannerRepository, Banner $model)
    {
        $this->bannerRepository = $bannerRepository;
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request)
    {
        $products = ProductTranslation::where('locale','az')->get();
        foreach ($products as $product)
        {
            $product->update(['locale' => 'en','min_qty' => 0,'max_qty' => 100,'brand_id' => 6,'unit_id' => 1,'tax' => 0]);
        }
        $banners = $this->bannerRepository->bannersPaginate($request->perPage ?? 15, true,  $request->type ?? null, $request->shop_id ?? null);
        return BannerResource::collection($banners);
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
     * Banner Products show .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function bannerProducts(int $id)
    {
        $banner = $this->bannerRepository->bannerDetails($id);
        if ($banner){
            $products = Product::with([
                'stocks.stockExtras.group.translation' => fn($q) => $q->actualTranslation(\request()->lang ?? 'en'),
                'category' => fn($q) => $q->select('id', 'uuid'),
                'category.translation' => fn($q) => $q->actualTranslation(\request()->lang ?? 'en')
                    ->select('id', 'category_id', 'locale', 'title'),
                'brand' => fn($q) => $q->select('id', 'uuid', 'title'),
                'unit.translation' => fn($q) => $q->actualTranslation(\request()->lang ?? 'en'),
                'translation' => fn($q) => $q->actualTranslation(\request()->lang ?? 'en'),
                'shop.translation' => fn($q) => $q->actualTranslation(\request()->lang ?? 'en')
            ])
                ->where('status',Product::PUBLISHED)
                ->whereIn('id', $banner->products)
                ->whereHas('shop', function ($item) {
                    $item->whereNull('deleted_at')->where('status', 'approved');
                })->paginate(15);

            return ProductResource::collection($products);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }


    public function likedBanner(int $id)
    {
        $banner = $this->model->find($id);
        if ($banner) {
            $banner->liked();
            return $this->successResponse(__('web.record_has_been_successfully_updated'), []);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

}
