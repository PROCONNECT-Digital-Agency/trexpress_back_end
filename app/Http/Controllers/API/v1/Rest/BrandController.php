<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Resources\BrandResource;
use App\Repositories\BrandRepository\BrandRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BrandController extends RestBaseController
{
    private BrandRepository  $brandRepository;
    /**
     * @param BrandRepository $brandRepository
     */
    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function paginate(Request $request)
    {
        $brands = $this->brandRepository->brandsPaginate($request->perPage ?? 15, true, $request->all());
        return BrandResource::collection($brands);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id)
    {
        $brand = $this->brandRepository->brandDetails($id);
        if ($brand){
            return $this->successResponse(__('errors.'. ResponseError::NO_ERROR), BrandResource::make($brand));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
