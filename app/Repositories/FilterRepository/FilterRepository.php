<?php

namespace App\Repositories\FilterRepository;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Repositories\CoreRepository;
use App\Traits\SetCurrency;
use Illuminate\Support\Facades\DB;

class FilterRepository extends CoreRepository
{
    use SetCurrency;

    private $lang;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }

    protected function getModelClass()
    {
        return Product::class;
    }

    public function productFilter($array = [])
    {
        $categoryIds = [];

        if (isset($array['categoryIds'])) {
            $categoryIds = Category::whereIn('id', $array['categoryIds'])->where('active',1)->pluck('id');
        }
        if (isset($array['parent_category_id']) && !isset($array['categoryIds'])) {
            $categoryIds = Category::with('children:id,parent_id')
                ->where('active',1)
                ->where('parent_id', $array['parent_category_id'])
                ->pluck('id');
        }
        // $array['range[0]'] is price from
        // $array['range[1]'] is price to
        if (isset($array['range'][0])) {
            $array['range'][0] /= $this->currency();
        }

        if (isset($array['range'][1])) {
            $array['range'][1] /= $this->currency();
        }

        $product = DB::table('products as p')
            ->leftJoin('shops as sh', 'sh.id', '=', 'p.shop_id')
            ->leftJoin('stocks as s', 's.countable_id', '=', 'p.id')
            ->leftJoin('stock_extras as s_e', 's.id', '=', 's_e.stock_id')
            ->when($categoryIds, function ($q) use ($categoryIds) {
                $q->whereIn('p.category_id', $categoryIds);
            })
            ->when(isset($array['range'][0]) || isset($array['range'][1]), function ($q) use ($array) {
                $q->whereBetween('price', [$array['range'][0] ?? 0.1, $array['range'][1] ?? 10000000000]);
            })
            ->where('sh.status', 'approved')
            ->whereNull('sh.deleted_at')
            ->whereNull('s.deleted_at')
            ->whereNull('p.deleted_at');

        $extraValueIds = $product->distinct('s_e.extra_value_id')->pluck('s_e.extra_value_id');

        $extraValues = DB::table('extra_values')
            ->whereIn('id', $extraValueIds)
            ->select('id', 'value', 'extra_group_id')
            ->get()
            ->groupBy('extra_group_id');

        if ($extraValues->count() <= 0) {
            $extraValues = null;
        }

        if (isset($array['extrasIds'])) {
            $product = $product->whereIn('s_e.extra_value_id', $array['extrasIds']);
        }

        $brandIds = $product->take(40)->distinct('p.brand_id')->pluck('p.brand_id');

        if (isset($array['brandIds'])) {
            $product->whereIn('p.brand_id', $array['brandIds']);
        }

        $categoryIds = $product->distinct('p.category_id')->pluck('p.category_id');

        if (isset($array['category_id'])) {
            $categoryIds = array_merge(array($array['category_id']), $categoryIds->toArray());
        }

        $shopIds = $product->distinct('p.shop_id')->pluck('p.shop_id');

        $brands = Brand::whereIn('id', $brandIds)->select('id', 'title')->get();

        $shops = Shop::whereIn('id', $shopIds)->with([
            'translation' => fn($q) => $q->actualTranslation($this->lang)->select('shop_id', 'title')
        ])->whereHas('translation', function ($q) {
            $q->actualTranslation($this->lang);
        })->select('id')->get();

        $categories = Category::whereIn('id', $categoryIds)->with([
            'translation' => fn($q) => $q->actualTranslation($this->lang)->select('category_id', 'title')
        ])->when(isset($array['category_id']), function ($q) use ($array) {
            $q->where('id', $array['category_id']);
        })->whereHas('translation', function ($q) {
            $q->actualTranslation($this->lang);
        })->where('active',1)->select('id')->get();


        return collect([
            'categories' => $categories,
            'shops' => $shops,
            'brands' => $brands,
            'extraValues' => $extraValues,
            'min_price' => $product->distinct('s.price')->min('s.price') * $this->currency(),
            'max_price' => $product->distinct('s.price')->max('s.price') * $this->currency()
        ]);
    }
}
