<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Exports\ProductExport;
use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\ReportChartRequest;
use App\Http\Requests\ReportPaginateRequest;
use App\Http\Resources\ProductResource;
use App\Imports\ProductImport;
use App\Jobs\ImportReadyNotify;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepoInterface;
use App\Repositories\ProductRepository\StockRepository;
use App\Services\ProductService\ProductAdditionalService;
use App\Services\ProductService\ProductService;
use App\Traits\Notification;
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

class ProductController extends SellerBaseController
{
    use Notification;

    /**
     * @param ProductService $productService
     * @param ProductRepoInterface $productRepository
     * @param StockRepository $stockRepository
     */
    public function __construct(private ProductService $productService, private ProductRepoInterface $productRepository, private StockRepository $stockRepository)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        if ($this->shop) {
            $products = $this->productRepository->productsPaginate($request->perPage ?? 15, $request->active ?? null, $request->all() + ['shop_id' => $this->shop->id]);
            return ProductResource::collection($products);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], request('lang')),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        if ($this->shop) {
            $result = $this->productService->create($request->merge(['shop_id' => $this->shop->id]));
            if ($result['status']) {
                return $this->successResponse(__('web.record_was_successfully_create'), ProductResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], request('lang')),
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], request('lang')),
                Response::HTTP_FORBIDDEN
            );
        }

    }

    /**
     * Display the specified resource.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid)
    {
        if (!$this->shop) {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], request('lang')),
                Response::HTTP_FORBIDDEN
            );
        }

        $product = $this->productRepository->productByUUID($uuid);

        if ($product) {
            return $this->successResponse(__('web.product_found'), ProductResource::make($product->load('translations')));
        }

        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request('lang')),
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
    public function update(Request $request, string $uuid)
    {
        if (!$this->shop) {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], request('lang')),
                Response::HTTP_FORBIDDEN
            );
        }

        $product = Product::query()->firstWhere('uuid', $uuid);

        if ($product && data_get($product, 'shop_id') == $this->shop->id) {

            $result = $this->productService->update(
                data_get($product, 'uuid'),
                $request->merge(['shop_id' => $this->shop->id])
            );

            if ($result['status']) {
                return $this->successResponse(
                    __('web.record_was_successfully_update'),
                    ProductResource::make($result['data'])
                );
            }

            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], request('lang')),
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->errorResponse(
            ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], request('lang')),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function destroy(string $uuid)
    {
        if ($this->shop) {
            $product = Product::query()->firstWhere('uuid', $uuid);
            if ($product && $product->shop_id == $this->shop->id) {
                $result = $this->productService->delete($product->uuid);

                if ($result['status']) {
                    return $this->successResponse(__('web.record_has_been_successfully_delete'));
                }
                return $this->errorResponse(
                    $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], request('lang')),
                    Response::HTTP_BAD_REQUEST
                );
            } else {
                return $this->errorResponse(
                    ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], request('lang')),
                    Response::HTTP_NOT_FOUND
                );
            }
        }

        return $this->errorResponse(
            ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], request('lang')),
            Response::HTTP_FORBIDDEN
        );
    }

    /**
     * Add Product Properties.
     *
     * @param string $uuid
     * @param Request $request
     * @return JsonResponse
     */
    public function addProductProperties(string $uuid, Request $request)
    {
        if ($this->shop) {
            $product = Product::firstWhere('uuid', $uuid);
            if ($product && $product->shop_id == $this->shop->id) {
                $result = (new ProductAdditionalService())->createOrUpdateProperties($product->uuid, $request->all());

                if ($result['status']) {
                    return $this->successResponse(__('web.record_has_been_successfully_created'), ProductResource::make($result['data']));
                }
                return $this->errorResponse(
                    $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], request('lang')),
                    Response::HTTP_BAD_REQUEST
                );
            } else {
                return $this->errorResponse(
                    ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], request('lang')),
                    Response::HTTP_NOT_FOUND
                );
            }
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], request('lang')),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Add Product Properties.
     *
     * @param string $uuid
     * @param Request $request
     * @return JsonResponse
     */
    public function addProductExtras(string $uuid, Request $request)
    {
        if ($this->shop) {
            $product = Product::firstWhere('uuid', $uuid);
            if ($product && $product->shop_id == $this->shop->id) {
                $result = (new ProductAdditionalService())->createOrUpdateExtras($product->uuid, $request->all());

                if ($result['status']) {
                    return $this->successResponse(__('web.record_has_been_successfully_created'), ProductResource::make($result['data']));
                }
                return $this->errorResponse(
                    $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], request('lang')),
                    Response::HTTP_BAD_REQUEST
                );
            } else {
                return $this->errorResponse(
                    ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], request('lang')),
                    Response::HTTP_NOT_FOUND
                );
            }
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], request('lang')),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Add Product Properties.
     *
     * @param string $uuid
     * @param Request $request
     */
    public function addInStock(string $uuid, Request $request)
    {
        if ($this->shop) {
            $product = Product::firstWhere('uuid', $uuid);
            if ($product && $product->shop_id == $this->shop->id) {
                // Polymorphic relation in Countable (Trait)
                $product->addInStock($request, $product->id);
                return $this->successResponse(__('web.record_has_been_successfully_created'), ProductResource::make($product));
            } else {
                return $this->errorResponse(
                    ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], request('lang')),
                    Response::HTTP_NOT_FOUND
                );
            }
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], request('lang')),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Search Model by tag name.
     *
     * @param Request $request
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function productsSearch(Request $request)
    {
        if ($this->shop) {
            $products = $this->productRepository->productsSearch($request->search ?? '', null, $this->shop->id);
            return ProductResource::collection($products);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], request('lang')),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Change Active Status of Model.
     *
     * @param string $uuid
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function setActive(string $uuid)
    {
        if ($this->shop) {
            $product = Product::firstWhere('uuid', $uuid);
            if ($product && $product->shop_id == $this->shop->id) {
                $product->update(['active' => !$product->active]);
                return $this->successResponse(__('web.record_has_been_successfully_updated'), ProductResource::make($product));
            } else {
                return $this->errorResponse(
                    ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], request('lang')),
                    Response::HTTP_NOT_FOUND
                );
            }
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], request('lang')),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function fileExport(Request $request)
    {
        if ($this->shop) {
            $fileName = 'export/product' . Str::slug(Carbon::now()->format('Y-m-d h:i:s')) . '.xlsx';
            $shop_id = $this->shop->id;
            $file = Excel::store(new ProductExport($shop_id,$request->all()), $fileName, 'public');
            if ($file) {
                return $this->successResponse('Successfully exported', [
                    'path' => 'public/export',
                    'file_name' => $fileName
                ]);
            }
            return $this->errorResponse('Error during export');
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], request('lang')),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function fileImport(Request $request)
    {
        if (!$this->shop) {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], request('lang')),
                Response::HTTP_FORBIDDEN
            );
        }

        $content = 'import';

        if (!Storage::exists("public/$content")) {
            Storage::makeDirectory("public/$content");
        }

        $filename = $request->file('file');
        $filename = Storage::put("public/$content", $filename);

        $filename = str_replace('public', 'storage', $filename);

        $import = new ProductImport($this->shop->id);

        $import->chain([
            new ImportReadyNotify($this->shop->id, $filename)
        ]);

        Excel::import($import, $filename);

        return $this->successResponse('Successfully imported');
    }

    public function deleteAll(Request $request)
    {
        if ($this->shop) {
            $result = $this->productService->deleteAll($request->productIds);
            if ($result) {
                return $this->successResponse(__('web.record_has_been_successfully_delete'));
            }
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], request('lang')),
                Response::HTTP_FORBIDDEN
            );
        }
    }


    public function productReportChart(ReportChartRequest $request)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->productRepository->productReportChartCache();

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function productReportPaginate(ReportPaginateRequest $request, FilterParamsRequest $filterParamsRequest)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->productRepository->reportPaginate($filterParamsRequest->get('perPage', 15));

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function productExtrasReport($product, ReportPaginateRequest $request)
    {
        try {
            $result = $this->productRepository->isPossibleCacheProductExtrasReport($product);

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function productStockReport($product, ReportPaginateRequest $request)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->stockRepository->productStockReportCache($product);

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function productReportCompare()
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->productRepository->productReportCompareCache();

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function stockReportPaginate(FilterParamsRequest $filterParamsRequest)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->stockRepository->stockReportPaginate($filterParamsRequest->get('perPage', 15));

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function variationsReportPaginate(ReportPaginateRequest $request, FilterParamsRequest $filterParamsRequest)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->stockRepository->variationsReportPaginate($filterParamsRequest->get('perPage', 15));

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function variationsReportChart(ReportChartRequest $request)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->stockRepository->variationsReportChartCache();

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function variationsReportCompare()
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->stockRepository->variationsReportCompareCache();

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function outOfStock(Request $request): AnonymousResourceCollection
    {
        $products = $this->productRepository->outOfStock($request->perPage ?? 15,$this->shop->id);

        return ProductResource::collection($products);
    }

}
