<?php

namespace App\Repositories\SearchRepository;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SearchRepository
{
    /**
     * @var array|Application|Request|string|null
     */
    protected $lang;

    public function __construct()
    {
        $this->lang = request('lang');
    }

    public function searchAll(array $array): Collection
    {
        $categories = Category::with([
            'translation' => fn($q) => $q->actualTranslation($this->lang)->select('id', 'category_id', 'title', 'locale'),
            'hasChildren' => fn($q) => $q->select('id', 'parent_id')
        ])
            ->when(isset($array['search']), function ($q) use ($array) {
                $q->whereHas('translation', function ($q) use ($array) {
                    $q->actualTranslation($this->lang)->where('title', 'LIKE', '%' . $array['search'] . '%');
                });
            })
            ->where('parent_id', '!=', 0)
            ->select('id', 'parent_id')
            ->where('active', 1)
            ->limit(10)->get();

        $brands = Brand::when(isset($array['search']), function ($q) use ($array) {
            $q->where('title', 'LIKE', '%' . $array['search'] . '%');
        })->where('active', 1)->select('id', 'title')->limit(10)->get();

        $products = Product::with([
            'translation' => fn($q) => $q->actualTranslation($this->lang)->select('id', 'product_id', 'title', 'locale'),
        ])->when(isset($array['search']), function ($q) use ($array) {
            $q->whereHas('translations', function ($q) use ($array) {
                $q->actualTranslation($this->lang)->where('title', 'LIKE', '%' . $array['search'] . '%');
            });
        })
            ->whereHas('stocks', function ($q) {
                $q->where('quantity', '>', 0);
            })
            ->whereHas('shop', function ($item) {
                $item->where('status', 'approved')->where('open',true);
            })
            ->whereHas('category')
            ->select('id', 'uuid')
            ->where('active',1)
            ->limit(10)->get();

        $shops = Shop::with([
            'translation' => fn($q) => $q->actualTranslation($this->lang)->select('id', 'shop_id', 'title', 'locale'),
        ])->when(isset($array['search']), function ($q) use ($array) {
            $q->whereHas('translations', function ($q) use ($array) {
                $q->actualTranslation($this->lang)->where('title', 'LIKE', '%' . $array['search'] . '%');
            });
        })
            ->where('status', Shop::APPROVED)->where('open',true)
            ->select('id', 'uuid')
            ->limit(10)->get();

        return collect([
            'categories' => $categories,
            'brands' => $brands,
            'products' => $products,
            'shops' => $shops
        ]);
    }
}
