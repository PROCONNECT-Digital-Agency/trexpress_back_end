<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use App\Models\PushNotification;
use App\Repositories\BlogRepository\BlogRepository;
use App\Services\BlogService\BlogService;
use App\Traits\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends AdminBaseController
{
    use Notification;
    private BlogRepository $blogRepository;
    private BlogService $blogService;

    /**
     * @param BlogRepository $blogRepository
     * @param BlogService $blogService
     */
    public function __construct(BlogRepository $blogRepository, BlogService $blogService)
    {
        parent::__construct();
        $this->blogRepository = $blogRepository;
        $this->blogService = $blogService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        $blogs = $this->blogRepository->blogsPaginate($request->perPage ?? 15, null, $request->all());
        return BlogResource::collection($blogs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $result = $this->blogService->create($request);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), BlogResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $uuid)
    {
        $blog = $this->blogRepository->blogByUUID($uuid);
        if ($blog){
            return $this->successResponse(__('web.brand_found'), BlogResource::make($blog->load('translations')));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param string $uuid
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(string $uuid, Request $request)
    {
        $result = $this->blogService->update($uuid, $request);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'), BlogResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $uuid)
    {
        $result = $this->blogService->delete($uuid);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang ?? 'en'),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Change Active Status of Model.
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function setActiveStatus(string $uuid)
    {
        $blog = Blog::firstWhere('uuid', $uuid);
        if ($blog) {
            $blog->update(['active' => !$blog->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), BlogResource::make($blog));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Change Active Status of Model.
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function blogPublish(string $uuid)
    {
        $blog = Blog::firstWhere('uuid', $uuid);
        if ($blog) {
            if (!isset($blog->published_at)){
                $blog->update(['published_at' => today()]);
            }
            if ($blog->type === 'notification') {
                $this->sendAllNotification(
                    $blog->translation?->short_desc ?? $blog->translation?->title,
                    [
                        'id'            => $blog->id,
                        'uuid'          => $blog->uuid,
                        'published_at'  => optional($blog->published_at)->format('Y-m-d H:i:s'),
                        'type'          => PushNotification::NEWS_PUBLISH
                    ],
                    $blog->translation?->title
                );
            }
            return $this->successResponse(__('web.record_has_been_successfully_updated'), BlogResource::make($blog));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
