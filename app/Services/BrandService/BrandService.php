<?php

namespace App\Services\BrandService;

use App\Helpers\FileHelper;
use App\Helpers\ResponseError;
use App\Models\Brand;
use App\Services\CoreService;
use App\Services\Interfaces\BrandServiceInterface;

class BrandService extends CoreService implements BrandServiceInterface
{

    protected function getModelClass()
    {
        return Brand::class;
    }

    public function create($collection)
    {
        try {
            $brand = $this->model()->create($this->setBrandParams($collection));
            if ($brand) {
                if (isset($collection->images)) {
                    $brand->update(['img' => $collection->images[0]]);
                    $brand->uploads($collection->images);
                }
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $brand];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => $e->getCode() ? 'ERROR_' . $e->getCode() : ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }

    }

    public function update(int $id, $collection)
    {
        try {
            $brand = $this->model()->find($id);
            if ($brand) {
                $brand->update($this->setBrandParams($collection));
                if (isset($collection->images)) {
                    $brand->update(['img' => $collection->images[0]]);
                    $brand->uploads($collection->images);
                }
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $brand];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => $e->getCode() ? 'ERROR_' . $e->getCode() : ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    public function delete(int $id)
    {
        $item = $this->model()->find($id);

        if ($item) {
            if (count($item->products) > 0) {
                return ['status' => false, 'code' =>  ResponseError::ERROR_507];
            }
            $item->delete();
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    private function setBrandParams($collection){
        return [
            'title' => $collection->title,
            'active' => $collection->active ?? 0,
        ];
    }
}
