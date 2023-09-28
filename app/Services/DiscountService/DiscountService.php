<?php

namespace App\Services\DiscountService;

use App\Helpers\ResponseError;
use App\Models\Discount;

class DiscountService extends \App\Services\CoreService
{

    protected function getModelClass()
    {
        return Discount::class;
    }

    /**
     *
     */
    public function create($collection): array
    {
        try {
            $discount = $this->model()->create($this->setDiscountParam($collection));
            if ($discount) {
                if (count($collection->products) > 0){
                    $discount->products()->attach($collection->products);
                }
                if (isset($collection->images)){
                    $discount->uploads($collection->images);
                    $discount->update(['img' => $collection->images[0]]);
                }

                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $discount];
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
        try {
            $discount = $this->model()->firstWhere(['id' => $id, 'shop_id' => $collection->shop_id]);
            if ($discount) {
                $discount->update($this->setDiscountParam($collection));
                if (count($collection->products) > 0){
                    $discount->products()->detach();
                    $discount->products()->attach($collection->products);
                }

                if (isset($collection->images)){
                    $discount->galleries()->delete();
                    $discount->uploads($collection->images);
                    $discount->update(['img' => $collection->images[0]]);
                }

                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $discount];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    /**
     *
     */
    public function setDiscountParam($collection): array
    {
        return [
            'shop_id' => $collection->shop_id ?? null,
            'type' => $collection->type ?? 'percent',
            'price' => $collection->price ?? 0,
            'start' => $collection->start ?? today(),
            'end' => $collection->end ?? '2060-01-01',
        ];
    }
}
