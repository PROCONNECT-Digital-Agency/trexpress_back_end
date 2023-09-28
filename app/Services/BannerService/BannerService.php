<?php

namespace App\Services\BannerService;

use App\Helpers\ResponseError;
use App\Models\Banner;
use App\Services\CoreService;

class BannerService extends CoreService
{

    /**
     * @return mixed
     */
    protected function getModelClass()
    {
        return Banner::class;
    }

    /**
     *
     */
    public function create($collection): array
    {
        try {
            $banner = $this->model()->create($this->setBannerParam($collection));
            if ($banner) {
                $this->setTranslations($banner, $collection);
                if (isset($collection->images)){
                    $banner->uploads($collection->images);
                    $banner->update(['img' => $collection->images[0]]);
                }

                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $banner];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    /**
     *
     */
    public function update($id, $collection): array
    {
        $banner = $this->model()->find($id);

        if ($banner) {
            try {
                $banner->update($this->setBannerParam($collection));
                $this->setTranslations($banner, $collection);

                if (isset($collection->images)){
                    $banner->galleries()->delete();
                    $banner->uploads($collection->images);
                    $banner->update(['img' => $collection->images[0]]);
                }
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $banner];

            } catch (\Exception $e) {
                return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
            }
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    /**
     *
     */
    public function setTranslations($model, $collection){

        if (isset($collection->title)) {
            $model->translations()->delete();

            foreach ($collection->title as $index => $value){
                if (isset($value) || $value != '') {
                    $model->translation()->create([
                        'title' => $value,
                        'description' => $collection->description[$index] ?? null,
                        'locale' => $index,
                    ]);
                }
            }
        }
    }

    /**
     *
     */
    public function setBannerParam($collection): array
    {
        return [
            'shop_id' => $collection->shop_id ?? null,
            'type' => $collection->type ?? 'banner',
            'url' => $collection->url ?? null,
            'products' => $collection->products ?? [],
        ];
    }
}