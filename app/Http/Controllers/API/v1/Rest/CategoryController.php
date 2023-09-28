<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Resources\CategoryCustomResource;
use App\Http\Resources\CategoryResource;
use App\Repositories\Interfaces\CategoryRepoInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends RestBaseController
{

    private CategoryRepoInterface $categoryRepo;

    /**
     * @param CategoryRepoInterface $categoryRepo
     */
    public function __construct(CategoryRepoInterface $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */

    public function paginate(Request $request) {
        $categories = $this->categoryRepo->parentCategories($request->perPage ?? 15, true,  $request->all());
        return CategoryResource::collection($categories);
    }


    /**
     * Display the specified resource.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $category = $this->categoryRepo->categoryByUuid($uuid,$active = true);
        if ($category){
            return $this->successResponse(__('errors.'. ResponseError::NO_ERROR), CategoryResource::make($category));
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
     * @return JsonResponse
     */
    public function categoriesSearch(Request $request): JsonResponse
    {
        $categories = $this->categoryRepo->categoriesSearch($request->search ?? '', true);
        return $this->successResponse(__('errors.'. ResponseError::NO_ERROR), CategoryResource::collection($categories));
    }

    public function products(Request $request): AnonymousResourceCollection
    {
        $categories = $this->categoryRepo->products($request->all(),$request->perPage ?? 15);
        return CategoryCustomResource::collection($categories);
    }



}
