<?php

namespace App\Services\ProductService;

use App\Helpers\ResponseError;
use App\Models\Product;

class ProductAdditionalService extends \App\Services\CoreService
{

    /**
     * @return mixed
     */
    protected function getModelClass()
    {
        return Product::class;
    }

    /**
     * @return mixed
     */
    public function createOrUpdateProperties(string $uuid, $array): array
    {
        $item = $this->model()->firstWhere('uuid', $uuid);
        if ($item) {
            try {
                $item->properties()->delete();
                foreach ($array['key'] as $i => $keys) {
                    foreach ($keys as $index => $key) {
                        $item->properties()->create([
                            'locale' => $index,
                            'key' => $key,
                            'value' => $array['value'][$i][$index]
                        ]);
                    }
                }
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $item];
            } catch (\Exception $e) {
                return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
            }
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    /**
     * @return mixed
     */
    public function createOrUpdateExtras(string $uuid, $array): array
    {
        $item = $this->model()->firstWhere('uuid', $uuid);
        if ($item) {
            try {
                $item->extras()->detach();
                $item->extras()->attach($array['extras']);
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $item];
            } catch (\Exception $e) {
                return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
            }
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }
}
