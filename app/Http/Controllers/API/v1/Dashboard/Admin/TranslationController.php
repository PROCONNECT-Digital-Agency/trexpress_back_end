<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Helpers\Utility;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\Translation\StoreRequest;
use App\Http\Resources\TranslationTableResource;
use App\Models\Language;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use phpDocumentor\Reflection\Types\Collection;
use Symfony\Component\HttpFoundation\Response;
use function PHPUnit\Framework\isEmpty;

class TranslationController extends AdminBaseController
{
    private Translation $model;

    public function __construct(Translation $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $translations = $this->model->filter($request->all())->get();
        return TranslationTableResource::collection($translations);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function paginate(FilterParamsRequest $request)
    {
        $perPage = $request->perPage ?? 15;
        $skip = $request->skip ?? 0;
        $search = $request->search ?? null;

        $translations = $this->model->filter($request->all())
            ->when(isset($search), function ($q) use ($search) {
                $q->where('key', 'LIKE', '%'. $search . '%');
            })
            ->orderBy($request->column ?? 'id', $request->sort ?? 'desc')
            ->get();

        $values = $translations->mapToGroups(function ($item){
            return [
                $item->key => [
                    'id' => $item->id,
                    'group' => $item->group,
                    'locale' => $item->locale,
                    'value' => $item->value,
                ]
            ];
        });

        $count = $values->count();
        $values = $values->skip($skip)->take($perPage);

        return $this->successResponse('errors.' . ResponseError::NO_ERROR, [
            'total' => $count,
            'perPage' => (int) $perPage,
            'translations' => $values
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $key = $this->model->where('key', $request->key)->first();
        if (!$key){
            try {
                foreach ($request->value as $index => $item) {
                    $this->model->create([
                        'group' => $request->group,
                        'key' => $request->key,
                        'locale' => $index,
                        'status' => $request->status ?? 1,
                        'value' => $item,
                    ]);
                    cache()->forget('language-' . $index);
                }

                return $this->successResponse(__('web.translation_created'), []);
            } catch (\Exception $exception) {
                return $this->errorResponse(
                    ResponseError::ERROR_404, $exception->getMessage(),
                    Response::HTTP_BAD_REQUEST
                );
            }
        }
        return $this->errorResponse(
            ResponseError::ERROR_506, trans('errors.' .ResponseError::ERROR_506, [], $request->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $translation = $this->model->find($id);
        if ($translation) {
            return $this->successResponse(__('web.translation_found'), TranslationTableResource::make($translation));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StoreRequest $request
     * @param $key
     * @return JsonResponse
     */
    public function update(Request $request, $key)
    {
        $translations = $this->model->where('key', $key)->get();
        if (count($translations) > 0){
            try {
                $this->model->where('key', $key)->delete();
                foreach ($request->value as $index => $item) {
                    $this->model->create([
                        'group' => $request->group,
                        'key' => $key,
                        'locale' => $index,
                        'value' => $item,
                    ]);
                    cache()->forget('language-' . $index);
                }

                return $this->successResponse(trans('errors.'. ResponseError::NO_ERROR), []);
            } catch (\Exception $exception) {
                return $this->errorResponse(
                    ResponseError::ERROR_400, $exception->getMessage(),
                    Response::HTTP_BAD_REQUEST
                );
            }
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' .ResponseError::ERROR_404, [], $request->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
