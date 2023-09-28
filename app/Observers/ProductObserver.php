<?php

namespace App\Observers;

use App\Helpers\ResponseError;
use App\Models\Product;
use App\Services\ProjectService\ProjectService;
use Illuminate\Support\Str;

class ProductObserver
{
    /**
     * Handle the Product "creating" event.
     *
     * @param \App\Models\Product $product
     * @return void
     * @throws \Exception
     */
    public function creating(Product $product)
    {
        $product->uuid = Str::uuid();
        $this->projectStatus();

    }

    /**
     * Handle the Product "created" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function created(Product $product)
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function updated(Product $product)
    {
        //
    }

    /**
     * Handle the Product "deleted" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function deleted(Product $product)
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function restored(Product $product)
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function forceDeleted(Product $product)
    {
        //
    }

    private function projectStatus(){
        if (!cache()->has('project.status') || cache('project.status')->active != 1){
            return (new ProjectService())->activationError();
        }
    }
}
