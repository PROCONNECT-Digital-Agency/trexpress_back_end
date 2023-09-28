<?php

namespace App\Services\PointDeliveryService;

use App\Helpers\ResponseError;
use App\Models\PointDelivery;
use App\Services\CoreService;
use App\Traits\SetTranslations;
use Illuminate\Support\Str;


class PointDeliveryService extends CoreService
{
    use SetTranslations;

    protected function getModelClass(): string
    {
        return PointDelivery::class;
    }

    /**
     * Create a new Shop model.
     * @param $collection
     * @return array
     */
    public function create($collection): array
    {
        $collection['location'] = [
            'latitude' => $collection['location'] ? Str::of($collection['location'])->before(',') : null,
            'longitude' => $collection['location'] ? Str::of($collection['location'])->after(',') : null,
        ];

        $model = $this->model()->create($collection);

        if ($model) {

            $this->setTranslations($model,$collection);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_501];

    }

    /**
     * Update specified Shop model.
     * @param array $collection
     * @param int $id
     * @return array
     */
    public function update(array $collection, int $id): array
    {
        $model = $this->model()->find($id);

        if ($model) {
            $collection['location'] = [
                'latitude' => $collection['location'] ? Str::of($collection['location'])->before(',') : null,
                'longitude' => $collection['location'] ? Str::of($collection['location'])->after(',') : null,
            ];
            $model->update($collection);

            $this->setTranslations($model,$collection);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    /**
     * @param array $ids
     * @return array
     */
    public function destroy(array $ids): array
    {
        $items = $this->model()->find($ids);

        if ($items->isNotEmpty()) {

            foreach ($items as $item) {
                $item->delete();
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }
}
