<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use App\Repositories\BlogRepository\BlogRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends RestBaseController
{
    private BlogRepository $blogRepository;

    public function __construct(BlogRepository $blogRepository)
    {
        $this->blogRepository = $blogRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request)
    {
        $blogs = $this->blogRepository->blogsPaginate(
            $request->perPage ?? 15, true, $request->merge(['published_at' => true])
        );

        return BlogResource::collection($blogs);
    }

    /**
     * Find Blog by UUID.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $uuid)
    {
        $blog = $this->blogRepository->blogByUUID($uuid);
        if ($blog){
            return $this->successResponse(__('errors.'. ResponseError::NO_ERROR), BlogResource::make($blog));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang ?? 'en'),
            Response::HTTP_NOT_FOUND
        );
    }

}
