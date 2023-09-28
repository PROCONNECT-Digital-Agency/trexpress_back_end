<?php

namespace App\Services\DeliveryManSettingServices;

use App\Helpers\ResponseError;
use App\Models\DeliveryManSetting;
use App\Services\CoreService;

class DeliveryManSettingService extends CoreService
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return DeliveryManSetting::class;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        /** @var DeliveryManSetting $deliveryManSetting */
        $deliveryManSetting = $this->model()->updateOrCreate([
            'user_id' => data_get($data, 'user_id')
        ], $data);

        if (data_get($data, 'images.0')) {
            $deliveryManSetting->uploads(data_get($data, 'images'));
            $deliveryManSetting->update(['img' => data_get($data, 'images.0')]);
        }

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $deliveryManSetting];
    }

    public function update(DeliveryManSetting $deliveryManSetting, array $data): array
    {
        $deliveryManSetting->update($data);

        if (data_get($data, 'images.0')) {
            $deliveryManSetting->galleries()->delete();
            $deliveryManSetting->uploads(data_get($data, 'images'));
            $deliveryManSetting->update(['img' => data_get($data, 'images.0')]);
        }

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $deliveryManSetting];

    }

    public function createOrUpdate(array $data): array
    {
        $data['user_id'] = auth('sanctum')->id();

        /** @var DeliveryManSetting $deliveryManSetting */
        $deliveryManSetting = $this->model()->updateOrCreate([
            'user_id' => $data['user_id']
        ], $data);

        if (data_get($data, 'images.0')) {
            $deliveryManSetting->galleries()->delete();
            $deliveryManSetting->uploads(data_get($data, 'images'));
            $deliveryManSetting->update(['img' => data_get($data, 'images.0')]);
        }

        return [
            'status' => true,
            'code' => ResponseError::NO_ERROR,
            'data' => $deliveryManSetting->loadMissing(['galleries', 'deliveryMan'])
        ];
    }

    public function updateLocation(array $data): array
    {
        $deliveryManSetting = DeliveryManSetting::where('user_id', auth('sanctum')->id())->first();

        if (!$deliveryManSetting){
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }
        $deliveryManSetting->update($data + ['updated_at' => now()]);

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $deliveryManSetting];
    }

    /**
     * @return array
     */
    public function updateOnline(): array
    {
        $deliveryManSetting = DeliveryManSetting::where('user_id', auth('sanctum')->id())->first();

        if (!$deliveryManSetting){
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }

        $deliveryManSetting->update([
            'online' => !$deliveryManSetting->online
        ]);


        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $deliveryManSetting];
    }

    public function delete(?array $ids = [])
    {

        $deliveryManSettings = DeliveryManSetting::whereIn('id', is_array($ids) ? $ids : [])->get();

        foreach ($deliveryManSettings as $deliveryManSetting) {
            $deliveryManSetting->delete();
        }

    }
}
