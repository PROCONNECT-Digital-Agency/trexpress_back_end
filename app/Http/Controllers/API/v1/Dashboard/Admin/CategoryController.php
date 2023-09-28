<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Exports\CategoryExport;
use App\Helpers\ResponseError;
use App\Http\Requests\Admin\Category\ChangePositionRequest;
use App\Http\Requests\CategoryCreateRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\CategoryResource;
use App\Imports\CategoryImport;
use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepoInterface;
use App\Services\CategoryServices\CategoryService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends AdminBaseController
{
    private CategoryService $categoryService;
    private CategoryRepoInterface $categoryRepository;

    public function __construct(CategoryService $categoryService, CategoryRepoInterface $categoryRepository)
    {
        parent::__construct();
        $this->categoryRepository = $categoryRepository;
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryRepository->parentCategories($request->all());
        return $this->successResponse(__('web.categories_list'), CategoryResource::collection($categories));
    }

    /**
     * Display a listing of the resource with paginate.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request): AnonymousResourceCollection
    {
        $categories = $this->categoryRepository->parentCategories($request->perPage ?? 15, null, $request->all());
        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CategoryCreateRequest $request
     * @return JsonResponse
     */
    public function store(CategoryCreateRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->categoryService->create($collection);

        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), []);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
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
        $category = $this->categoryRepository->categoryByUuid($uuid);
        if ($category) {
            $category->load('translations')->makeHidden('translation');
            return $this->successResponse(__('web.category_found'), CategoryResource::make($category));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param string $uuid
     * @param CategoryUpdateRequest $request
     * @return JsonResponse
     */
    public function update(string $uuid, CategoryUpdateRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->categoryService->update($uuid, $collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'), []);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
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
        $result = $this->categoryService->delete($uuid);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove Model image from storage.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function imageDelete(string $uuid): JsonResponse
    {
        $category = Category::firstWhere('uuid', $uuid);
        if ($category) {
            Storage::disk('public')->delete($category->img);
            $category->update(['img' => null]);

            return $this->successResponse(__('web.image_has_been_successfully_delete'), $category);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Search Model by tag name.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function categoriesSearch(Request $request): AnonymousResourceCollection
    {
        $categories = $this->categoryRepository->categoriesSearch($request->search ?? '');
        return CategoryResource::collection($categories);
    }

    /**
     * Change Active Status of Model.
     *
     * @param string $uuid
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function setActive(string $uuid): JsonResponse|AnonymousResourceCollection
    {
        $category = $this->categoryRepository->categoryByUuid($uuid);
        if ($category) {
            $category->update(['active' => !$category->active]);

            return $this->successResponse(__('web.record_has_been_successfully_updated'), CategoryResource::make($category));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function fileExport(): JsonResponse|AnonymousResourceCollection
    {
        $fileName = 'export/categories' . Str::slug(Carbon::now()->format('Y-m-d h:i:s')) . '.xlsx';
        $file = Excel::store(new CategoryExport(), $fileName, 'public');
        if ($file) {
            return $this->successResponse('Successfully exported', [
                'path' => 'public/export',
                'file_name' => $fileName
            ]);
        }
        return $this->errorResponse('Error during export');
    }

    public function fileImport(Request $request): JsonResponse|AnonymousResourceCollection
    {
        Excel::import(new CategoryImport(), $request->file);
        return $this->successResponse('Successfully imported');
    }

    public function reportChart(): JsonResponse|AnonymousResourceCollection
    {
        $result = $this->categoryRepository->categoriesReportChartCache();

        return $this->successResponse('', $result);
    }

    public function reportPaginate(FilterParamsRequest $filterParamsRequest): JsonResponse
    {
        $result = $this->categoryRepository->reportPagination($filterParamsRequest->get('perPage', 15));

        return $this->successResponse('', $result);
    }

    public function reportCompare(): JsonResponse
    {
        $result = $this->categoryRepository->reportCompareCache();

        return $this->successResponse('', $result);
    }

    public function setPosition(ChangePositionRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->categoryService->setPosition($collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'), []);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function checkPosition(ChangePositionRequest $request): JsonResponse
    {

        $collection = $request->validated();
        $result = $this->categoryService->checkPosition($collection);
        if (!$result['status']) {
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->successResponse($result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language));

    }
}
