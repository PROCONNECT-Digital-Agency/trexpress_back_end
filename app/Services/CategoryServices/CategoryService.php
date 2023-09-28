<?php

namespace App\Services\CategoryServices;

use App\Helpers\ResponseError;
use App\Models\Category;
use App\Services\CoreService;
use App\Services\Interfaces\CategoryServiceInterface;
use Exception;

class CategoryService extends CoreService implements CategoryServiceInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Category::class;
    }

    /**
     * @param array $collection
     * @return array
     */
    public function create(array $collection): array
    {
        try {
            $parentId = data_get($collection, 'parent_id', 0);

            $parentCategory = Category::find($parentId);

            if (data_get($parentCategory, 'product')) {
                return ['status' => false, 'code' => ResponseError::ERROR_111];
            }

            /** @var Category $category */
            $category = $this->model()->create($collection);

            if (isset($collection['position'])){
                $oldPositionModel = $this->model()
                    ->where('position',$collection['position'])
                    ->where('id','!=',$category->id)
                    ->first();

                if ($oldPositionModel){
                    $oldPositionModel->update([
                        'position' => null
                    ]);
                }
            }


            $this->setTranslations($category, $collection);

            if (data_get($collection, 'images.0')) {
                $category->update(['img' => data_get($collection, 'images.0', '')]);
                $category->uploads(data_get($collection, 'images', []));
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        } catch (Exception $e) {
            return ['status' => false, 'code' => $e->getCode() ? 'ERROR_' . $e->getCode() : ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }

    }

    /**
     * @param string $uuid
     * @param $collection
     * @return array
     */
    public function update(string $uuid, $collection): array
    {
        try {
            $parentId = data_get($collection, 'parent_id', 0);

            $parentCategory = Category::find($parentId);

            if (data_get($parentCategory, 'product')) {
                return ['status' => false, 'code' => ResponseError::ERROR_111];
            }

            $category = $this->model()->firstWhere('uuid', $uuid);
            if ($category) {
                if (isset($collection['position'])){
                    $oldPositionModel = $this->model()
                        ->where('position',$collection['position'])
                        ->where('id','!=',$category->id)
                        ->first();

                    if ($oldPositionModel){
                        $oldPositionModel->update([
                            'position' => $category->position
                        ]);
                    }
                }


                $category->update($collection);
                $this->setTranslations($category, $collection);
                if (isset($collection['images'])) {
                    $category->galleries()->delete();
                    $category->uploads($collection['images']);
                    $category->update(['img' => $collection['images'][0]]);
                }
                return ['status' => true, 'code' => ResponseError::NO_ERROR];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        } catch (Exception $e) {
            return ['status' => false, 'code' => $e->getCode() ? 'ERROR_' . $e->getCode() : ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param string $uuid
     * @return array
     */
    public function delete(string $uuid): array
    {
        $item = $this->model()->firstWhere('uuid', $uuid);
        if ($item) {
            if (count($item->children) > 0) {
                return ['status' => false, 'code' => ResponseError::ERROR_504];
            }
            $item->delete();
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }


    public function setTranslations($model, $collection)
    {
        $model->translations()->delete();

        foreach ($collection['title'] as $index => $value) {
            if (isset($value) || $value != '') {
                $model->translation()->create([
                    'title' => $value,
                    'description' => data_get($collection, "description.$index"),
                    'locale' => $index,
                ]);
            }
        }
    }

    public function setPosition(array $array,int $id): array
    {
        $model = $this->model()->find($id);

        $oldModel = $this->model()->where('position',$array['position'])->where('id','!=',$id)->first();

        if ($oldModel && isset($model->position)){

            $oldModel->update([
                'position' => $model->position
            ]);

            $model->update([
                'position' => $array['position']
            ]);

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }

        if ($model){

            $model->update([
                'position' => $array['position']
            ]);
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function checkPosition(array $array): array
    {
        if (isset($array['id'])){
            $model = $this->model()->find($array['id']);
        }

        $oldModel = $this->model()->where('position',$array['position'])->first();

        if ($oldModel && isset($model?->position)){
            return ['status' => false, 'code' => ResponseError::ERROR_214];
        }

        if ($oldModel){
            return ['status' => false, 'code' => ResponseError::ERROR_215];
        }

        return ['status' => true, 'code' => ResponseError::NO_ERROR];
    }
}
