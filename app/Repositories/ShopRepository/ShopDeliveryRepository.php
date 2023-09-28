<?php

namespace App\Repositories\ShopRepository;

use App\Models\Shop;
use Illuminate\Support\Facades\Cache;

class ShopDeliveryRepository extends \App\Repositories\CoreRepository
{
    private $lang;

    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }

    protected function getModelClass()
    {
        return Shop::class;
    }

    public function findNearbyShops($clientLocation, $shopLocation = null){

        $client =  explode(',', $clientLocation);
        $locations = Cache::remember('shops-location', 84600, function (){
            return $this->model()->select('id', 'location', 'delivery_range')->get();
        });

        $ids = [];
        foreach ($locations as $locate) {
            $radius_earth = 6371;  // Радиус Земли
            $lat_1 = deg2rad($client[0]);
            $lon_1 = deg2rad($client[1]);
            $lat_2 = deg2rad(round($locate->location['latitude'], 5));
            $lon_2 = deg2rad(round($locate->location['longitude'], 5));
            $d = 2 * $radius_earth * asin(sqrt(sin(($lat_2 - $lat_1) / 2) ** 2 + cos($lat_1) * cos($lat_2) * sin(($lon_2 - $lon_1) / 2) ** 2));

            if ($locate->delivery_range >= round($d, 2)){
                $ids = array_merge($ids, [$locate->id]);
            }
        }

        return $this->model()->updatedDate($this->updatedDate)
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang);
            })
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->lang)
            ])
            ->when(isset($shopLocation), function ($q) use ($shopLocation) {
                $shop = explode(',', $shopLocation);
                $q->where(['location->latitude' => $shop[0], 'location->longitude' => $shop[1]]);
            }, function ($q) use ($ids) {
                $q->whereIn('id', $ids);
            })
            ->where(['status' => 'approved', 'visibility' => 1])->get();
    }
}
