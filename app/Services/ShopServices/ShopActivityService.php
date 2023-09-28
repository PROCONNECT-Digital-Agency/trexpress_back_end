<?php

namespace App\Services\ShopServices;

use App\Helpers\ResponseError;
use App\Models\Shop;
use App\Services\CoreService;

class ShopActivityService extends CoreService
{
    /**
     * @return mixed
     */
    protected function getModelClass()
    {
        return Shop::class;
    }

    public function changeStatus(string $uuid,  $status)
    {
        $shop = $this->model()->firstWhere('uuid', $uuid);
        if ($shop) {
            $shop->update(['status' => $status]);
            if ($status == 'approved'){
                $shop->seller->syncRoles('seller');
            }
            return ['status' => true, 'message' => ResponseError::NO_ERROR, 'data' => $shop];
        }
        return ['status' => false, 'message' => ResponseError::ERROR_404];
    }

    public function changeOpenStatus(string $uuid)
    {
        $shop = $this->model()->firstWhere('uuid', $uuid);
        $shop->update(['open' => !$shop->open]);
    }

    public function changeVisibility(string $uuid)
    {
        $shop = $this->model()->firstWhere('uuid', $uuid);
        $shop->update(['visibility' => !$shop->visibility]);
    }

}
