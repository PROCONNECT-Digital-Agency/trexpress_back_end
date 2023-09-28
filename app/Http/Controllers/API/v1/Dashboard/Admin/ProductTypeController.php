<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductTypeResource;
use App\Repositories\ProductTypeRepository\ProductTypeRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class ProductTypeController extends Controller
{
    use ApiResponse;

    private ProductTypeRepository $repository;

    public function __construct(ProductTypeRepository $repository){
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $productTypes = $this->repository->productsTypeList($request->search);

        if ($productTypes)
            return ProductTypeResource::collection($productTypes->paginate($request->perPage));
        else
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND
            );
    }
}
