<?php

namespace App\Http\Controllers\API\v1\Rest;
use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryResource;
use App\Http\Resources\ShopResource;
use App\Models\Delivery;
use App\Models\Settings;
use App\Models\Shop;
use App\Repositories\Interfaces\ShopRepoInterface;
use App\Repositories\ShopRepository\ShopDeliveryRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShopController extends RestBaseController
{
    private ShopRepoInterface $shopRepository;

    /**
     * @param ShopRepoInterface $shopRepository
     */
    public function __construct(ShopRepoInterface $shopRepository)
    {
        $this->shopRepository = $shopRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        $shops = $this->shopRepository->shopsPaginate($request->perPage ?? 15,
            $request->merge([
            'status' => 'approved',
            'visibility' => 1,
        ])->all());

        return ShopResource::collection($shops);
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
            return $this->successResponse(__('web.shop_found'), ShopResource::make($shop));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Search shop Model from database.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function shopsSearch(Request $request)
    {
        $shops = $this->shopRepository->shopsSearch($request->search ?? '', [
            'status' => 'approved',
            'visibility' => 1,
        ]);
        return ShopResource::collection($shops);
    }

    public function nearbyShops(Request $request)
    {
        $shops = (new ShopDeliveryRepository())->findNearbyShops($request->clientLocation, $request->shopLocation ?? null);
        return $this->successResponse(__('web.list_of_shops'), ShopResource::collection($shops));
    }

    /**
     * Search shop Model from database.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function shopsDeliveryByIDs(Request $request)
    {
        $delivery = Settings::adminSettings()->where('key', 'delivery')->first();
            if (isset($delivery) && $delivery->key == 'delivery' && (int) $delivery->value == 1){
                $shops = $this->shopRepository->shopsByIDs($request->shops ?? [], 'approved');
                return ShopResource::collection($shops);
            } else {
                $shops = Delivery::with([
                        'translation' => fn($q) => $q->actualTranslation($request->lang),
                ])->where('active',true)->whereNull('shop_id')->get();

                return DeliveryResource::collection($shops);
            }
    }

    /**
     * Search shop Model from database via IDs.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function shopsByIDs(Request $request)
    {
        $shops = $this->shopRepository->shopsByIDs($request->shops, 'approved');
        return ShopResource::collection($shops);
    }
}
