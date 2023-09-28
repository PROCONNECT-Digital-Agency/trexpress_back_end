<?php

namespace App\Services\UserServices;

use App\Helpers\ResponseError;
use App\Models\Currency;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Str;

class UserWalletService extends \App\Services\CoreService
{
    protected function getModelClass()
    {
        return Wallet::class;
    }

    public function create($user) {
        try {
            $wallet = new Wallet();
            $wallet->uuid = Str::uuid();
            $wallet->user_id = $user->id;
            $wallet->currency_id = Currency::whereDefault(1)->pluck('id')->first();
            $wallet->price = 0;
            $wallet->save();

        } catch (\Exception $exception) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $exception->getMessage()];
        }
    }

    public function update($user, $array) {
        try {
            $user->wallet()->update([
                'price' => $user->wallet->price + $array['price'],
            ]);
            $this->historyCreate($user->wallet, $array);
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        } catch (\Exception $exception) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $exception->getMessage()];
        }
    }

    public function historyCreate($wallet, $array){
        $wallet->histories()->create([
            'uuid' => Str::uuid(),
            'transaction_id' => $array['transaction_id'] ?? null,
            'type' => $array['type'] ?? 'topup',
            'price' => $array['price'],
            'note' => $array['note'] ?? null,
            'created_by' => auth('sanctum')->id(),
        ]);
    }
}
