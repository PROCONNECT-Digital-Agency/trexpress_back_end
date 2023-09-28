<?php

namespace App\Services\UserServices;

use App\Helpers\ResponseError;
use App\Models\Order;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\CoreService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UserAddressService extends CoreService
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass()
    {
        return UserAddress::class;
    }

    public function create($collection): array
    {
        $address = $this->model()->create($this->setAddressParams($collection));

        $this->setDefault($address->id, $address->default);

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $address];
    }


    public function update($id, $collection): array
    {
        $model = $this->model()->find($id);
        if ($model) {
            $model->update($this->setAddressParams($collection));

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function destroy(int $id)
    {
        $model = $this->model()->find($id);

        if ($model) {
            $addressExists = $model->orders()->whereIn('status', [
                Order::NEW,
                Order::READY,
                Order::ON_A_WAY,
            ])->exists();

            if ($addressExists) {
                return ['status' => false, 'code' => ResponseError::ERROR_433];
            }

            $model->delete();

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    /**
     * Set User Address model parameters for actions
     */
    private function setAddressParams($collection): array
    {
        return [
            'user_id' => $collection['user_id'],
            'title' => $collection['title'],
            'location' => [
                'latitude' => $collection['location'] ? Str::of($collection['location'])->before(',') : null,
                'longitude' => $collection['location'] ? Str::of($collection['location'])->after(',') : null,
            ],
            'active' => $collection['active'] ?? 0,
            'address' => $collection['address'] ?? null,
            'apartment' => $collection['apartment'] ?? null,
            'postcode' => $collection['postcode'] ?? null,
            'city' => $collection['city'] ?? null,
            'note' => $collection['note'] ?? null,
            'email' => $collection['email'] ?? null,
        ];
    }

    public function setAddressDefault(int $id = null, int $default = null): array
    {
        $item = $this->model()->where(['user_id' => auth('sanctum')->id(), 'id' => $id])->first();
        if ($item) {
            return $this->setDefault($id, $default, auth('sanctum')->id());
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }
}
