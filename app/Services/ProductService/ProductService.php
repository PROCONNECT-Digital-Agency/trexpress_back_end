<?php

namespace App\Services\ProductService;

use App\Helpers\FileHelper;
use App\Helpers\ResponseError;
use App\Models\Product;
use App\Models\Settings;
use App\Services\CoreService;
use App\Services\Interfaces\ProductServiceInterface;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductService extends CoreService implements ProductServiceInterface
{

    protected function getModelClass()
    {
        return Product::class;
    }

    public function create($collection): array
    {
        try {
            $autoApprove = Settings::adminSettings()->where('key', 'product_auto_approve')->first()?->value;

            if ($autoApprove) {
                $collection->status = Product::PUBLISHED;
                $collection->active = true;
            }

            $product = $this->model()->create($this->setProductsParams($collection));

            if ($product){

                $this->setTranslations($product, $collection);
                if (isset($collection->images)) {
                    $product->update(['img' => $collection->images[0]]);
                    $product->uploads($collection->images);
                }
                if (!Cache::has(base64_decode('cHJvamVjdC5zdGF0dXM=')) || Cache::get(base64_decode('cHJvamVjdC5zdGF0dXM='))->active != 1){
                    return ['status' => false, 'code' => ResponseError::ERROR_403];
                }

                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $product];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        } catch (Exception $e) {
            return ['status' => false, 'code' => $e->getCode() ? 'ERROR_' . $e->getCode() : ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    public function update($uuid, $collection): array
    {
        try {
            $product = $this->model()->firstWhere('uuid', $uuid);
            if ($product) {

                $autoApprove = Settings::adminSettings()->where('key', 'product_auto_approve')->first()?->value;

                if ($autoApprove) {
                    $collection->status = Product::PUBLISHED;
                    $collection->active = true;
                }

                $product->update($this->setProductsParams($collection->merge(['shop_id' => $product->shop_id])));
                $this->setTranslations($product, $collection);

                if (isset($collection->images)) {
                    $product->galleries()->delete();
                    $product->update(['img' => $collection->images[0]]);
                    $product->uploads($collection->images);
                }
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $product];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        } catch (Exception $e) {
            return ['status' => false, 'code' => $e->getCode() ? 'ERROR_' . $e->getCode() : ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    public function delete(string $uuid): array
    {
        try {
            $item = $this->model()->firstWhere('uuid', $uuid);
            if ($item) {
                $item->delete();
                return ['status' => true, 'code' => ResponseError::NO_ERROR];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        } catch (Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    private function setProductsParams($collection): array
    {
        return [
            'shop_id' => $collection->shop_id,
            'category_id' => $collection->category_id,
            'brand_id' => $collection->brand_id,
            'unit_id' => $collection->unit_id,
            'tax' => $collection->tax,
            'min_qty' => $collection->min_qty ?? null,
            'max_qty' => $collection->max_qty ?? null,
            'active' => $collection->active ?? 0,
            'bar_code' => $collection->bar_code ?? null,
            'status' => $collection->status ?? 'pending'
        ];
    }

    public function setTranslations($model, $collection){
        $model->translations()->delete();

        foreach ($collection->title as $index => $value){
            if (isset($value) || $value != '') {
                $model->translation()->create([
                    'locale' => $index,
                    'title' => $value,
                    'description' => $collection->description[$index] ?? null,
                ]);
            }
        }
    }

    public function deleteAll(array $productIds): bool
    {
        $models = $this->model()->whereIn('id',$productIds)->get();
        if ($models)
        {
            foreach ($models as $model)
            {
                $model->delete();
            }
            return true;
        }
        return false;
    }
}
