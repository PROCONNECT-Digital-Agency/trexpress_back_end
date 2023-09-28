<?php

namespace App\Services\CouponService;

use App\Helpers\ResponseError;
use App\Models\Coupon;
use App\Services\CoreService;
use Exception;

class CouponService extends CoreService
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass()
    {
        return Coupon::class;
    }

    public function create($collection): array
    {
        try {
            $coupon = $this->model()->create($collection);
            $this->setTranslations($coupon, $collection);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $coupon];
        } catch (Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param int $id
     * @param $collection
     * @return array
     */
    public function update(int $id, $collection): array
    {
        $coupon = $this->model()->find($id);
        if ($coupon) {
            try {
                $coupon->update($collection);
                $this->setTranslations($coupon, $collection);
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $coupon];
            }
            catch (Exception $e) {
                return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
            }
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }


    public function setTranslations($model, $collection)
    {
        $model->translations()->delete();
        foreach ($collection['title'] as $index => $value){
            $model->translation()->create([
                'title' => $value,
                'description' => $collection->description[$index] ?? null,
                'locale' => $index,
            ]);
        }
    }
}
