<?php

namespace App\Observers;

use App\Helpers\ResponseError;
use App\Models\Invitation;
use App\Models\Shop;
use App\Models\User;
use App\Services\ProjectService\ProjectService;
use Illuminate\Support\Str;

class ShopObserver
{

    /**
     * Handle the Shop "creating" event.
     *
     * @param \App\Models\Shop $shop
     * @return void
     * @throws \Exception|\Psr\SimpleCache\InvalidArgumentException
     */
    public function creating(Shop $shop)
    {
        $shop->uuid = Str::uuid();

        $this->projectStatus();
    }

    /**
     * Handle the Shop "created" event.
     *
     * @param  \App\Models\Shop  $shop
     * @return void
     */
    public function created(Shop $shop)
    {

    }

    /**
     * Handle the Shop "updated" event.
     *
     * @param  \App\Models\Shop  $shop
     * @return void
     */
    public function updated(Shop $shop)
    {
        if ($shop->status == 'approved') {
            $shop->seller->syncRoles('seller');
            $shop->seller->invitations()->delete();
        }
    }

    /**
     * Handle the Shop "deleted" event.
     *
     * @param  \App\Models\Shop  $shop
     * @return void
     */
    public function deleted(Shop $shop)
    {
        $shop->seller->syncRoles('user');
        if ($shop->invitations->isNotEmpty())
        {
            $userIds = $shop->invitations->pluck('user_id')->toArray();
            $users = User::find($userIds);
            foreach ($users as $user){
                $user->syncRoles('user');
            }
            $shop->invitations()->delete();
        }
    }

    /**
     * Handle the Shop "restored" event.
     *
     * @param  \App\Models\Shop  $shop
     * @return void
     */
    public function restored(Shop $shop)
    {
        //
    }

    /**
     * Handle the Shop "force deleted" event.
     *
     * @param  \App\Models\Shop  $shop
     * @return void
     */
    public function forceDeleted(Shop $shop)
    {
        //
    }

    private function setUUID(Shop $shop){

    }

    private function projectStatus(){
        if (!cache()->has('project.status') || cache('project.status')->active != 1){
            return (new ProjectService())->activationError();
        }
    }
}
