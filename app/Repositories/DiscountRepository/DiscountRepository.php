<?php

namespace App\Repositories\DiscountRepository;

use App\Models\Discount;

class DiscountRepository extends \App\Repositories\CoreRepository
{
    private $lang;


    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }

    protected function getModelClass()
    {
        return Discount::class;
    }

    public function discountsPaginate($perPage, $shop = null, $active = null, $array = [])
    {
        return $this->model()
            ->filter($array)
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })
            ->orderBy($array['column'] ?? 'id', $array['sort'] ?? 'desc')
            ->paginate($perPage);
    }

    public function discountDetails($id, $shop = null)
    {
        return $this->model()->with([
            'galleries',
            'products.translation' => function($q) {
                $q->select('id', 'product_id', 'locale', 'title')
                ->actualTranslation($this->lang);
            }])
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })->find($id);
    }

}
