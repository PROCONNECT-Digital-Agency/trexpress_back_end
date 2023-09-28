<?php

namespace App\Repositories\PointDeliveryRepository;

use App\Models\PointDelivery;
use App\Repositories\CoreRepository;

class PointDeliveryRepository extends CoreRepository
{
    protected mixed $lang;

    /**
     */
    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }

    protected function getModelClass(): string
    {
        return PointDelivery::class;
    }

    public function paginate($array)
    {
        return $this->model()
            ->with('translation','shop.translation')
            ->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->where('shop_id', $array['shop_id']);
            })
            ->paginate($array['perPage']);
    }


    public function show(int $id)
    {
        return $this->model()->with('translation','translations','shop.translation')->find($id);
    }
}
