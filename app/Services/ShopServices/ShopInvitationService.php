<?php

namespace App\Services\ShopServices;

use App\Helpers\ResponseError;
use App\Models\Shop;

class ShopInvitationService extends \App\Services\CoreService
{
    protected function getModelClass(): string
    {
        return Shop::class;
    }

    public function createInvitation(string $uuid, int $id): array
    {
       $shop = $this->model()->with('users')->firstWhere(['uuid' => $uuid, 'id' => $id]);
       if ($shop) {
           $shop->invitations()->updateOrCreate(['user_id' => auth()->id()], [
               'status' => 'new',
           ]);

           return  ['status' => true, 'code' => ResponseError::NO_ERROR];
       }
       return ['status' => false, 'code' => ResponseError::ERROR_404];
    }
}
