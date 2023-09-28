<?php


namespace App\Helpers;


use App\Models\ParcelOrderSetting;
use App\Models\Shop;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class Utility
{
    /* получение уникальных данных в массиве */
    public static function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    /* Pagination for massive */
    public static function paginate($items, $perPage, $page = null, $options = [])
    {
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }


    /**
     * @param float|null $km
     * @param Shop|null $shop
     * @param float|null $rate
     * @return float|null
     */
    public function getPriceByDistance(?float $km, ?Shop $shop, ?float $rate): ?float
    {
        $price      = data_get($shop, 'price', 0);
        $pricePerKm = data_get($shop, 'price_per_km');
        return round(($price + ($pricePerKm * $km)) * $rate, 2);
    }

    /**
     * @param array $origin, Адрес селлера (откуда)
     * @param array $destination, Адрес клиента (куда)
     * @return float|int
     */
    public function getDistance(array $origin, array $destination): float|int
    {

        if (count($origin) !== 2 || count($destination) !== 2) {
            return 0;
        }

        $originLat          = $this->toRadian(data_get($origin, 'latitude'));
        $originLong         = $this->toRadian(data_get($origin, 'longitude'));
        $destinationLat     = $this->toRadian(data_get($destination, 'latitude'));
        $destinationLong    = $this->toRadian(data_get($destination, 'longitude'));

        $deltaLat           = $destinationLat - $originLat;
        $deltaLon           = $originLong - $destinationLong;

        $delta              = pow(sin($deltaLat / 2), 2);
        $cos                = cos($destinationLong) * cos($destinationLat);

        $sqrt               = ($delta + $cos * pow(sin($deltaLon / 2), 2));
        $asin               = 2 * asin(sqrt($sqrt));

        $earthRadius        = 6371;

        return (string)$asin != 'NAN' ? round($asin * $earthRadius, 2) : 1;
    }

    private function toRadian($degree = 0): ?float
    {
        return $degree * pi() / 180;
    }

    public static function pointInPolygon(array $point, array $vs): bool
    {
        $x = data_get($point, 'latitude');
        $y = data_get($point, 'longitude');

        $inside = false;

        for ($i = 0, $j = count($vs) - 1; $i < count($vs); $j = $i++) {

            $xi = $vs[$i][0];
            $yi = $vs[$i][1];
            $xj = $vs[$j][0];
            $yj = $vs[$j][1];

            $intersect = (($yi > $y) != ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }

        }

        return $inside;
    }

    /**
     * @param int $typeId
     * @param float|null $km
     * @param float|null $rate
     * @return float|null
     */
    public function getParcelPriceByDistance(int $typeId, ?float $km, ?float $rate): ?float
    {
        $type       = ParcelOrderSetting::find($typeId);
        $price      = $type->special ? $type->special_price : $type->price;
        $pricePerKm = $type->special ? $type->special_price_per_km : $type->price_per_km;

        return round(($price + ($pricePerKm * $km)) * $rate, 2);
    }
}
