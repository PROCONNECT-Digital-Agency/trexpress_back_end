<?php

namespace App\Services\SubscriptionService;

use App\Helpers\ResponseError;
use App\Models\Subscription;

class SubscriptionService extends \App\Services\CoreService
{
    protected function getModelClass()
    {
        return Subscription::class;
    }

    public function update($id, $collection){
        try {
            $subscript = $this->model()->find($id);
            if ($subscript){
                $subscript->update($this->setSubscriptionParams($collection));

                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $subscript];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    private function setSubscriptionParams($collection): array
    {
        return [
            'type' => $collection->type ?? 'orders',
            'price' => $collection->price,
            'month' => $collection->month,
            'active' => $collection->active ?? 0,
        ];
    }
}
