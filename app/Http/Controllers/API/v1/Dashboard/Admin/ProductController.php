<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Exports\ProductExport;
use App\Helpers\ResponseError;
use App\Http\Requests\Admin\Product\StatusRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\ProductResource;
use App\Imports\ProductImport;
use App\Jobs\ImportReadyNotify;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepoInterface;
use App\Repositories\ProductRepository\StockRepository;
use App\Services\ProductService\ProductAdditionalService;
use App\Services\ProductService\ProductService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AdminBaseController
{
    /**
     * @param ProductService $productService
     * @param ProductRepoInterface $productRepository
     * @param StockRepository $stockRepository
     */
    public function __construct(private ProductService $productService,private ProductRepoInterface $productRepository,private StockRepository $stockRepository)
    {
        parent::__construct();
    }

    public function paginate(Request $request): AnonymousResourceCollection
    {
        $products = $this->productRepository
            ->productsPaginate($request->perPage ?? 15, $request->active, $request->all());

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $result = $this->productService->create($request);

        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_create'), ProductResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], request('lang')),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $product = $this->productRepository->productByUUID($uuid);

        if ($product) {
            return $this->successResponse(__('web.product_found'), ProductResource::make($product->load('translations')));
        }

        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request('lang')),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $result = $this->productService->update($uuid, $request);

        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_update'), ProductResource::make($result['data']));
        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], request('lang')),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        $result = $this->productService->delete($uuid);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], request('lang')),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Add Product Properties.
     *
     * @param string $uuid
     * @param Request $request
     * @return JsonResponse
     */
    public function addProductProperties(string $uuid, Request $request): JsonResponse
    {
        $result = (new ProductAdditionalService())->createOrUpdateProperties($uuid, $request->all());

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_created'), ProductResource::make($result['data']));
        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], request('lang')),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Add Product Properties.
     *
     * @param string $uuid
     * @param Request $request
     * @return JsonResponse
     */
    public function addProductExtras(string $uuid, Request $request): JsonResponse
    {
        $result = (new ProductAdditionalService())->createOrUpdateExtras($uuid, $request->all());

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_created'), ProductResource::make($result['data']));
        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], request('lang')),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Add Product Properties.
     *
     * @param string $uuid
     * @param Request $request
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function addInStock(string $uuid, Request $request): JsonResponse|AnonymousResourceCollection
    {
        /** @var Product $product */
        $product = Product::query()->firstWhere('uuid', $uuid);

        if ($product) {
            // Polymorphic relation in Countable (Trait)
            $product->addInStock($request, data_get($product, 'id'));
            return $this->successResponse(
                __('web.record_has_been_successfully_created'),
                ProductResource::make($product)
            );
        }

        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request('lang')),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Search Model by tag name.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function productsSearch(Request $request): AnonymousResourceCollection
    {
        $categories = $this->productRepository->productsSearch($request->search ?? '');

        return ProductResource::collection($categories);
    }

    /**
     * Change Active Status of Model.
     *
     * @param string $uuid
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function setActive(string $uuid): JsonResponse|AnonymousResourceCollection
    {
        $product = $this->productRepository->productByUUID($uuid);

        if ($product) {
            $product->update(['active' => !$product->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), ProductResource::make($product));
        }

        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request('lang')),
            Response::HTTP_NOT_FOUND
        );
    }

    public function fileExport(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $fileName = 'export/product'.Str::slug(Carbon::now()->format('Y-m-d h:i:s')).'.xlsx';

        $file = Excel::store(new ProductExport($request->input('shop_id'),$request->all()), $fileName, 'public');

        if ($file) {
            return $this->successResponse('Successfully exported', [
                'path' => 'public/export',
                'file_name' => $fileName
            ]);
        }

        return $this->errorResponse('Error during export');
    }

    public function fileImport(ExportRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $collection = $request->validated();

        $filename = $request->file('file');

        $import = new ProductImport($collection['shop_id']);

        $import->chain([
            new ImportReadyNotify($collection['shop_id'])
        ]);

        Excel::import($import, $filename);

        return $this->successResponse('Successfully imported');
    }

    public function deleteAll(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $result = $this->productService->deleteAll($request->input('productIds', []));

        if ($result) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }

        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request('lang')),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Change Active Status of Model.
     *
     * @param string $uuid
     * @param StatusRequest $request
     * @return JsonResponse
     */
    public function setStatus(string $uuid, StatusRequest $request): JsonResponse
    {

        /** @var Product $product */
        $collection = $request->validated();

        $product = $this->productRepository->productByUUID($uuid);

        if (!$product) {
            return $this->onErrorResponse([
                'code'      => ResponseError::ERROR_404,
                'message'   => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        if ($product->stocks?->sum('quantity') === 0 && $collection['status'] === Product::PUBLISHED) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_430]);
        }

        $product->update([
            'status' => $collection['status']
        ]);

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ProductResource::make($product)
        );
    }

    public function productReportChart(): JsonResponse|AnonymousResourceCollection
    {
        try {
            $result = $this->productRepository->productReportChartCache();

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function productReportCompare(): JsonResponse|AnonymousResourceCollection
    {
        try {
            $result = $this->productRepository->productReportCompareCache();

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function productReportPaginate(FilterParamsRequest $filterParamsRequest): JsonResponse|AnonymousResourceCollection
    {
        try {
            $result = $this->productRepository->reportPaginate($filterParamsRequest->get('perPage', 15));
            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function productStockReport($product): JsonResponse|AnonymousResourceCollection
    {
        try {
            $result = $this->stockRepository->productStockReportCache($product);

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function productExtrasReport($product): JsonResponse|AnonymousResourceCollection
    {
        try {
            $result = $this->productRepository->isPossibleCacheProductExtrasReport($product);

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function stockReportPaginate(FilterParamsRequest $filterParamsRequest): JsonResponse|AnonymousResourceCollection
    {
        try {
            $result = $this->stockRepository->stockReportPaginate($filterParamsRequest->get('perPage', 15));

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function variationsReportPaginate(FilterParamsRequest $filterParamsRequest): JsonResponse|AnonymousResourceCollection
    {
        try {
            $result = $this->stockRepository->variationsReportPaginate($filterParamsRequest->get('perPage', 15));

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function variationsReportChart(): JsonResponse|AnonymousResourceCollection
    {
        try {
            $result = $this->stockRepository->variationsReportChartCache();

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function variationsReportCompare(): JsonResponse|AnonymousResourceCollection
    {
        try {
            $result = $this->stockRepository->variationsReportCompareCache();

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function outOfStock(Request $request): AnonymousResourceCollection
    {
        $products = $this->productRepository->outOfStock($request->perPage ?? 15);

        return ProductResource::collection($products);
    }

}
