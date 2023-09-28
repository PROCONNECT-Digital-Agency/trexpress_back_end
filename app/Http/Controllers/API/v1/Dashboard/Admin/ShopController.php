<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\ReportChartRequest;
use App\Http\Requests\ReportCompareRequest;
use App\Http\Requests\ReportPaginateRequest;
use App\Http\Requests\ShopCreateRequest;
use App\Http\Requests\ShopStatusChangeRequest;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use App\Repositories\Interfaces\ShopRepoInterface;
use App\Repositories\ShopRepository\ShopDeliveryRepository;
use App\Services\Interfaces\ShopServiceInterface;
use App\Services\ShopServices\ShopActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ShopController extends AdminBaseController
{
    private ShopServiceInterface $shopService;
    private ShopRepoInterface $shopRepository;

    public function __construct(ShopServiceInterface $shopService, ShopRepoInterface $shopRepository)
    {
        parent::__construct();
        $this->shopService = $shopService;
        $this->shopRepository = $shopRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $shops = $this->shopRepository->shopsList($request->all());
        return $this->successResponse(__('web.shop_list'), ShopResource::collection($shops));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        $shops = $this->shopRepository->shopsPaginate($request->perPage ?? 15, $request->all());
        return ShopResource::collection($shops);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ShopCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ShopCreateRequest $request)
    {
        $shop = Shop::where('user_id', $request->user_id ?? auth('sanctum')->id())->first();
        if (!$shop) {
            $result = $this->shopService->create($request);
            if ($result['status']) {
                return $this->successResponse(__('web.record_successfully_created'), ShopResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->errorResponse(
           ResponseError::ERROR_206, trans('errors.' . ResponseError::ERROR_206, [], \request()->lang ?? 'en'),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $uuid)
    {
        $shop = $this->shopRepository->shopDetails($uuid);
        if ($shop){
            $shop->load('translations');
            return $this->successResponse(__('web.shop_found'), ShopResource::make($shop));
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
     * @param  string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $uuid)
    {
        $shop = Shop::where(['user_id' => $request->user_id, 'uuid' => $uuid])->first();
        if ($shop) {
            $result = $this->shopService->update($uuid, $request);
            if ($result['status']) {
                return $this->successResponse(__('web.record_successfully_updated'), $result['data']);
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->errorResponse(
            ResponseError::ERROR_207, trans('errors.' . ResponseError::ERROR_207, [], \request()->lang ?? 'en'),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $uuid)
    {
        $result = $this->shopService->delete($uuid);
        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Search shop Model from database.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function shopsSearch(Request $request)
    {
        $categories = $this->shopRepository->shopsSearch($request->search ?? '');
        return ShopResource::collection($categories);
    }

    /**
     * Remove Model image from storage.
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function imageDelete(string $uuid)
    {
        $validator = Validator::make(\request()->all(), [
            'tag' => ['required',Rule::in('background','logo')]
        ]);
        if ($validator->fails()) {
            return $this->errorResponse(
                ResponseError::ERROR_400, $validator->errors()->first(),
                Response::HTTP_BAD_REQUEST
            );
        }
        $tag = request()->tag;
        $shop = Shop::firstWhere('uuid', $uuid);
        if ($shop) {
            Storage::disk('public')->delete($shop->img);
            $shop->update([$tag . '_img' => null]);

            return $this->successResponse(__('web.image_has_been_successfully_delete'), $shop);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Change Shop Status.
     *
     * @param string $uuid
     * @param ShopStatusChangeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statusChange(string $uuid, ShopStatusChangeRequest $request){
        $result = (new ShopActivityService())->changeStatus($uuid, $request->status);
        if ($result['status']){
            return $this->successResponse(__('web.shop_status_change'), []);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    public function nearbyShops(Request $request)
    {
        $shops = (new ShopDeliveryRepository())->findNearbyShops($request->clientLocation, $request->shopLocation ?? null);
        return $this->successResponse(__('web.list_of_shops'), ShopResource::collection($shops));
    }

    public function getWithSeller(FilterParamsRequest $filterParamsRequest)
    {
        try {
            $result = $this->shopRepository->getShopWithSellerCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function reportPaginate(ReportPaginateRequest $request, FilterParamsRequest $filterParamsRequest)
    {
        try {
            $result = $this->shopRepository->reportPaginateCache($filterParamsRequest->get('perPage', 15));

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function reportChart(ReportChartRequest $request)
    {
        try {
            $result = $this->shopRepository->reportChartCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function reportCompare()
    {
        try {
            $result = $this->shopRepository->reportCompareCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }
}
