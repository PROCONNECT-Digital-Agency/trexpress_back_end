<?php

namespace App\Repositories\CouponRepository;

use App\Models\Coupon;
use App\Repositories\CoreRepository;

class CouponRepository extends CoreRepository
{
    private $lang;
    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }

    protected function getModelClass()
    {
        return Coupon::class;
    }

    public function couponsList($array)
    {
        return $this->model()->filter($array)->get();
    }

    public function couponsPaginate($perPage)
    {
        return $this->model()->whereHas('translation', function ($q) {
            $q->where('locale', $this->lang);
        })
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->lang)
            ])
            ->paginate($perPage);
    }

    public function couponByName(string $name)
    {
        return $this->model()->with([
            'galleries',
            'translation' => function($q) {
                $q->actualTranslation($this->lang);
            }
        ])->firstWhere('name', $name);
    }

    public function couponById(int $id, $shop = null)
    {
        return $this->model()->with([
            'shop',
            'galleries',
            'translation' => function($q) {
                $q->actualTranslation($this->lang);
            }
        ])
            ->find($id);
    }
}
