<?php

namespace App\Repositories\WalletRepository;

use App\Models\WalletHistory;
use App\Repositories\CoreRepository;

class WalletHistoryRepository extends CoreRepository
{

    /**
     * @return mixed
     */
    protected function getModelClass()
    {
        return WalletHistory::class;
    }

    public function walletHistoryPaginate($perPage, $array = [])
    {
        return $this->model()->with('author', 'user')
            ->when(isset($array['wallet_uuid']), function ($q) use ($array) {
                $q->where('wallet_uuid', $array['wallet_uuid']);
            })
            ->when(isset($array['status']), function ($q) use ($array) {
                $q->where('status', $array['status']);
            })
            ->when(isset($array['type']), function ($q) use ($array) {
                $q->where('type', $array['type']);
            })
            ->orderBy($array['column'] ?? 'created_at', $array['sort'] ?? 'desc')
            ->paginate($perPage);
    }
}