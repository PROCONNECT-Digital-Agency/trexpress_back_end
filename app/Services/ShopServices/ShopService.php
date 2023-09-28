<?php

namespace App\Services\ShopServices;

use App\Helpers\FileHelper;
use App\Helpers\ResponseError;
use App\Models\Shop;
use App\Models\User;
use App\Services\CoreService;
use App\Services\Interfaces\ShopServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ShopService extends CoreService implements ShopServiceInterface
{

    protected function getModelClass()
    {
        return Shop::class;
    }

    /**
     * Create a new Shop model.
     * @param $collection
     * @return array
     */
    public function create($collection): array
    {
        try {
            $shop = $this->model()->create($this->setShopParams($collection));
            if ($shop){
                User::find($shop->user_id)?->syncRoles('seller');
                $this->setTranslations($shop, $collection);
                $this->setImages($shop, $collection);

                Cache::forget('shops-location');
                if (!Cache::has(base64_decode('cHJvamVjdC5zdGF0dXM=')) || Cache::get(base64_decode('cHJvamVjdC5zdGF0dXM='))->active != 1){
                    return ['status' => false, 'code' => ResponseError::ERROR_403];
                }

                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $shop];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => $e->getCode() ? 'ERROR_' . $e->getCode() : ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }

    }

    /**
     * Update specified Shop model.
     * @param string $uuid
     * @param $collection
     * @return array
     */
    public function update(string $uuid, $collection): array
    {
        try {
            $shop = $this->model()->firstWhere('uuid', $uuid);
            if ($shop) {

                if ($shop->user_id !== $collection['user_id']){
                        User::find($shop->user_id)?->syncRoles('user');
                    }

                User::find($collection['user_id'])?->syncRoles('seller');

                $item = $shop->update($this->setShopParams($collection));

                if ($item){

                    $this->setTranslations($shop, $collection);
                    $this->setImages($shop, $collection);

                    return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $shop->load([
                        'translation' => fn($q) => $q->actualTranslation($this->language),
                        'seller'])];
                }
            }
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => $e->getCode() ? 'ERROR_' . $e->getCode() : ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete Shop model.
     * @param string $uuid
     * @return array
     */
    public function delete(string $uuid): array
    {
        $item = $this->model()->firstWhere('uuid', $uuid);
        if ($item) {
            FileHelper::deleteFile($item->img);
            $item->delete();

            Cache::forget('shops-location');
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    /**
     * Set params for Shop to update or create model.
     * @return array
     */
    private function setShopParams($collection)
    {

        return [
            'user_id' => $collection['user_id'] ?? auth('sanctum')->id(),
            'tax' => $collection['tax'] ?? 0,
            'delivery_range' => $collection['delivery_range'] ?? 0,
            'percentage' => $collection['percentage'] ?? 0,
            'min_amount' => $collection['min_amount'] ?? 0,
            'location' => [
                'latitude' => $collection['location'] ? Str::of($collection['location'])->before(',') : null,
                'longitude' => $collection['location'] ? Str::of($collection['location'])->after(',') : null,
            ],
            'phone' => $collection['phone'] ?? null,
            'open' => $collection['open'] ?? 0,
            'open_time' => $collection['open_time'] ?? '6:00:00',
            'close_time' => $collection['close_time'] ?? '18:00:00',
            'show_type' => $collection['show_type'] ?? 0,
          //  'status' => $collection['status'] ?? 'new',
            'status_note' => $collection['status_note'] ?? null,
            'mark' => $collection['mark'] ?? null,
        ];
    }

    /**
     * Update or Create Shop translations if model was changed.
     * @param Shop $model
     * @param $collection
     * @return void
     */
    public function setTranslations(Shop $model, $collection)
    {
        $model->translations()->delete();

        foreach ($collection['title'] as $index => $value){
            if (isset($value) || $value != '') {
                $model->translation()->create([
                    'locale' => $index,
                    'title' => $value,
                    'description' => $collection['description'][$index] ?? null,
                    'address' => $collection['address'][$index] ?? null,
                ]);
            }
        }
    }

    /**
     * Update or Create Shop images if model was changed
     * @param $shop
     * @param $collection
     * @return void
     */
    public function setImages($shop, $collection): void
    {
        if (isset($collection->images)) {
            $shop->galleries()->delete();
            foreach ($collection->images as $image) {
                if (Str::of($image)->contains('shops/logo/')) {
                    $shop->update(['logo_img' => $image]);
                }
                if (Str::of($image)->contains('shops/background/')){
                    $shop->update(['background_img' => $image]);
                }
            }
            $shop->uploads($collection->images);
        }
    }
}
