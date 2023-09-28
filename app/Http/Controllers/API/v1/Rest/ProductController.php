<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Requests\Filter\FilterRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\ProductResource;
use App\Models\Point;
use App\Repositories\CategoryRepository\CategoryRepository;
use App\Repositories\Interfaces\ProductRepoInterface;
use App\Repositories\OrderRepository\OrderDetailRepository;
use App\Repositories\ProductRepository\RestProductRepository;
use App\Repositories\ShopRepository\ShopRepository;
use App\Services\ProductService\ProductReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends RestBaseController
{
    private ProductRepoInterface $productRepository;
    private RestProductRepository $restProductRepository;

    public function __construct(RestProductRepository $restProductRepository, ProductRepoInterface $productRepository)
    {
        $this->middleware('sanctum.check')->only('addProductReview');
        $this->productRepository = $productRepository;
        $this->restProductRepository = $restProductRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterRequest $request)
    {
        $collection = $request->validated();
        $collection['rest'] = true;

        $products = $this->productRepository->productFilter($collection);
        return ProductResource::collection($products);
    }

    /**
     * Display the specified resource.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid)
    {
        request()->merge([
            'review' => true,
        ]);
        $product = $this->productRepository->productByUUID($uuid);
        if ($product) {
            return $this->successResponse(__('errors.' . ResponseError::NO_ERROR), ProductResource::make($product));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    public function productsByShopUuid(FilterParamsRequest $request, string $uuid)
    {
        $shop = (new ShopRepository())->shopDetails($uuid);
        if ($shop) {
            $products = $this->productRepository->productsPaginate($request->perPage ?? 15, true, ['shop_id' => $shop->id, 'rest' => true]);
            return ProductResource::collection($products);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    public function productsByBrand(FilterParamsRequest $request, int $id)
    {
        $products = $this->productRepository->productsPaginate($request->perPage ?? 15, true, ['brand_id' => $id, 'rest' => true]);
        return ProductResource::collection($products);
    }

    public function productsByCategoryUuid(FilterParamsRequest $request, string $uuid)
    {
        $category = (new CategoryRepository())->categoryByUuid($uuid);
        if ($category) {
            $products = $this->productRepository->productsPaginate($request->perPage ?? 15, true, ['category_id' => $category->id, 'rest' => true]);
            return ProductResource::collection($products);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );

    }

    /**
     * Search Model by tag name.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function productsSearch(Request $request)
    {
        $products = $this->productRepository->productsSearch($request->search ?? '', true);
        return ProductResource::collection($products);
    }

    public function mostSoldProducts(FilterParamsRequest $request)
    {
        $products = $this->restProductRepository->productsMostSold($request->perPage ?? 4, $request->all());

        return ProductResource::collection($products);
    }

    /**
     * Search Model by tag name.
     *
     * @param string $uuid
     * @param Request $request
     * @return JsonResponse
     */
    public function addProductReview(string $uuid, Request $request): JsonResponse
    {
        $result = (new ProductReviewService())->addReview($uuid, $request);
        if ($result['status']) {
            return $this->successResponse(ResponseError::NO_ERROR, []);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    public function discountProducts(Request $request)
    {
        $products = $this->restProductRepository->productsDiscount($request->perPage ?? 15, $request->all());
        return ProductResource::collection($products);
    }

    public function productsCalculate(Request $request)
    {
        $result = (new OrderDetailRepository())->orderProductsCalculate($request->all());
        return $this->successResponse(__('web.products_calculated'), $result);
    }

    /**
     * Get Products by IDs.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function productsByIDs(Request $request)
    {
        $products = $this->productRepository->productsByIDs($request->products);
        return ProductResource::collection($products);
    }

    public function checkCashback(Request $request)
    {

        $point = Point::getActualPoint($request->amount ?? 0);
        return $this->successResponse(__('web.cashback'), ['price' => $point]);
    }

    public function getByBrandId(Request $request,int $id): AnonymousResourceCollection
    {
        $products = $this->restProductRepository->getByBrandId($request->perPage ?? 15,$id);
        return ProductResource::collection($products);
    }
}
