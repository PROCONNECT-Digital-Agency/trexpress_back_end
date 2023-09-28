<?php

namespace App\Repositories\ProductRepository;

use App\Models\Product;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RestProductRepository extends CoreRepository
{

    private string $lang;

    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }

    protected function getModelClass()
    {
        return Product::class;
    }

    public function  productsMostSold($perPage, $array = [])
    {
//        $page = 1;
//        if (isset($array['page'])){
//            $page = $array['page'];
//        }
//        return Cache::remember('most-sold'.'-'.$perPage.'-'.$page ?? null,3600,function () use ($array ,$perPage){
            return $this->model()->filter($array)->updatedDate($this->updatedDate)
                ->whereHas('translation', function ($q) {
                    $q->where('locale', $this->lang);
                })
                ->without('countable')
                ->withAvg('reviews', 'rating')
                ->withCount('orders')
                ->with([
                    'stocks.countable',
                    'stocks.discount',
                    'translation' => fn($q) => $q->where('locale', $this->lang)
                        ->select('id', 'product_id', 'locale', 'title'),
                ])
                ->whereHas('stocks', function ($item){
                    $item->where('quantity', '>', 0)->where('price', '>', 0);
                })
                ->whereHas('shop', function ($item) {
                    $item->whereNull('deleted_at')->where('status', 'approved')->where('open',true);
                })
                ->orderBy('orders_count','desc')
                ->where('img','!=',null)
                ->whereHas('category')
                ->whereHas('orders')
                ->whereActive(1)
                ->where('status',Product::PUBLISHED)
                ->paginate($perPage);
//        });

    }

    /**
     * @param $perPage
     * @param array $array
     * @return array|Application|Request|string|null
     */
    public function productsDiscount($perPage, array $array = [])
    {
        $profitable = isset($array['profitable']) ? '=' : '>=';

        return $this->model()->filter($array)->updatedDate($this->updatedDate)
            ->whereHas('discount', function ($item) use ($profitable) {
                $item->where('active', 1)
                    ->whereDate('start', '<=', today())
                    ->whereDate('end', $profitable, today()->format('Y-m-d'));
            })
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang);
            })
            ->whereHas('stocks', function ($item){
                $item->where('quantity', '>', 0)->where('price', '>', 0);
            })
            ->withAvg('reviews', 'rating')
            ->whereHas('category')
            ->with([
                'stocks' => fn($q) => $q->where('quantity', '>', 0)->where('price', '>', 0),
                'stocks.stockExtras.group.translation' => fn($q) => $q->where('locale', $this->lang),
                'translation' => fn($q) => $q->where('locale', $this->lang)
                    ->select('id', 'product_id', 'locale', 'title'),
                'category' => fn($q) => $q->select('id', 'uuid'),
                'category.translation' => fn($q) => $q->where('locale', $this->lang)
                    ->select('id', 'category_id', 'locale', 'title'),
                'brand' => fn($q) => $q->select('id', 'title'),
            ])
            ->whereHas('shop', function ($item) {
                $item->whereNull('deleted_at')->where('status', 'approved')->where('open',true);
            })
            ->where('img','!=',null)
            ->whereActive(1)
            ->where('status',Product::PUBLISHED)
            ->paginate($perPage);
    }

    public function getByBrandId($perPage,int $brandId)
    {
        return $this->model()->with([
            'stocks' => fn($q) => $q->where('quantity', '>', 0)->where('price', '>', 0),
            'translation' => fn($q) => $q->actualTranslation($this->lang),
        ])->whereHas('stocks', function ($item) {
            $item->where('quantity', '>', 0)->where('price', '>', 0);
        })
            ->whereHas('shop', function ($item) {
                $item->whereNull('deleted_at')->where('status', 'approved')->where('open',true);
            })
            ->where('status',Product::PUBLISHED)
            ->where('brand_id',$brandId)
            ->paginate($perPage );
    }
}
