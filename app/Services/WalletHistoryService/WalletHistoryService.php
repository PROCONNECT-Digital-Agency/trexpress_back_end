<?php

namespace App\Services\WalletHistoryService;

use App\Helpers\ResponseError;
use App\Models\WalletHistory;
use App\Services\CoreService;
use Illuminate\Support\Str;

class WalletHistoryService extends CoreService
{

    protected function getModelClass(): string
    {
        return WalletHistory::class;
    }

    public function create($user, $collection): array
    {
        try {
            $walletHistory = $this->model()->create([
                'uuid' => Str::uuid(),
                'wallet_uuid' => $user->wallet->uuid,
                'type' => $collection->type ?? 'withdraw',
                'price' => $collection->price,
                'note' => $collection->note,
                'created_by' => $user->id,
                'status' =>  $collection->status ?? 'processed',
            ]);

            if (isset($collection->type) && $collection->type == 'withdraw') {
                $user->wallet()->update(['price' => $user->wallet->price - $collection->price]);
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $walletHistory];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    public function changeStatus(string $uuid, string $status = null): array
    {
        $walletHistory = $this->model()->firstWhere('uuid', $uuid);
        if ($walletHistory) {
            if ($walletHistory->status == 'processed') {
                $walletHistory->update(['status' => $status]);

                if ($status == 'rejected' || $status == 'canceled') {
                    $walletHistory->wallet()->update(['price' => $walletHistory->wallet->price + $walletHistory->price]);
                }
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }
}
