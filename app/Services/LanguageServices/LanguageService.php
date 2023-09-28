<?php

namespace App\Services\LanguageServices;

use App\Helpers\FileHelper;
use App\Helpers\ResponseError;
use App\Models\Language as Model;
use App\Services\CoreService;
use App\Services\Interfaces\LanguageServiceInterface;

class LanguageService extends CoreService implements LanguageServiceInterface
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    protected function getModelClass()
    {
        return Model::class;
    }

    public function create($collection)
    {
        try {
            $language = $this->model()->create($this->setLanguageParams($collection));
            $this->setDefault($language->id, $collection->default);

            if ($language) {
                if (isset($collection->images)) {
                    $language->update(['img' => $collection->images[0]]);
                    $language->uploads($collection->images);
                }

                cache()->forget('languages-list');
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $language];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    /**
     *
     */
    public function update($id, $collection)
    {
        try {
            $model = $this->model()->find($id);
            if ($model) {
                $model->update($this->setLanguageParams($collection));

                $default =  $model->default ?: $collection->default;
                $this->setDefault($id, $default);

                if (isset($collection->images)) {
                    $model->galleries()->delete();
                    $model->update(['img' => $collection->images[0]]);
                    $model->uploads($collection->images);
                }

                cache()->forget('languages-list');
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_404];

        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    /**
     *
     */
    public function delete($id)
    {
        try {
            $item = $this->model()->find($id);
            if ($item) {
                if ($item->default) {
                    return ['status' => false, 'code' => ResponseError::ERROR_505];
                }
                FileHelper::deleteFile('images/languages/'.$item->img);
                $item->delete();

                cache()->forget('languages-list');
                return ['status' => true, 'code' => ResponseError::NO_ERROR];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }

    }

    /**
     * Set Language model parameters for actions
     */
    private function setLanguageParams($collection){
        return [
            'title' => $collection->title,
            'locale' => $collection->locale,
            'backward' => $collection->backward ?? 0,
            'active' => $collection->active ?? 0,
        ];
    }

    public function setLanguageDefault(int $id = null, int $default = null)
    {
        $item = $this->model()->find($id);
        if ($item) {
            cache()->forget('languages-list');
            return $this->setDefault($id, $default);
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

}
