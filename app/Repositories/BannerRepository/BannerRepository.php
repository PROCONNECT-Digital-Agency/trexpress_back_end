<?php

namespace App\Repositories\BannerRepository;

use App\Models\Banner;

class BannerRepository extends \App\Repositories\CoreRepository
{
    private $lang;
    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }

    protected function getModelClass()
    {
        return Banner::class;
    }

    public function bannersPaginate($perPage, $active = null, $type = null, $shop = null)
    {
        return $this->model()->query()
            ->withCount('likes')
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->lang)
                    ->select('id', 'locale', 'banner_id', 'title')
            ])
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })
            ->when(isset($type), function ($q) use ($type) {
                $q->where('type', $type);
            })
            ->paginate($perPage);
    }

    public function bannerDetails(int $id)
    {
        return $this->model()
            ->withCount('likes')
            ->with([
            'galleries', 'shop',
            'translation' => fn($q) => $q->where('locale', $this->lang)
            ])
            ->find($id);
    }
}
