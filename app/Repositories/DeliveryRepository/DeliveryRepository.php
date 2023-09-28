<?php

namespace App\Repositories\DeliveryRepository;

use App\Models\Delivery;
use App\Repositories\CoreRepository;

class DeliveryRepository extends CoreRepository
{
    private $lang;

    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }

    protected function getModelClass(): string
    {
        return Delivery::class;
    }

    public function deliveriesList($shop = null, $active = null, $array = [])
    {
        return $this->model()->whereHas('translation', function ($q) {
            $q->where('locale', $this->lang);
        })
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->lang)
            ])
            ->filter($array)
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })->get();
    }

    public function deliveriesPaginate($perPage, $shop = null, $active = null, $array = [])
    {
        return $this->model()->with([
            'translation' => function ($q) {
                $q->where(['locale' => $this->lang]);
            }])
            ->filter($array)
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })->paginate($perPage);
    }

    public function deliveryDetails($id, $shop = null)
    {
        return $this->model()->with([
            'translation' => function ($q) {
                $q->where(['locale' => $this->lang]);
            }])
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })->find($id);
    }

    public function deliveryDetailOrder($id)
    {
        return $this->model()->with([
            'translation' => function ($q) {
                $q->where(['locale' => $this->lang]);
            }
            ])
            ->where('active',1)
            ->find($id);
    }

    public function deliveriesListForOrder($array = [])
    {
        return $this->model()->whereHas('translation', function ($q) {
            $q->where('locale', $this->lang);
        })
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->lang),
            ])
            ->where('active',1)
            ->paginate($array['perPage']);
    }

}
