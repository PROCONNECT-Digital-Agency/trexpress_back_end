<?php

namespace App\Repositories\ProductRepository;

use App\Exports\ProductsReportExport;
use App\Http\Resources\CompareResource;
use App\Jobs\ExportJob;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Stock;
use App\Traits\SetCurrency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ProductRepository extends \App\Repositories\CoreRepository implements \App\Repositories\Interfaces\ProductRepoInterface
{
    use SetCurrency;

    private string $lang;

    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }

    protected function getModelClass(): string
    {
        return Product::class;
    }

    public function productsList($active = null, $array = [])
    {
        return $this->model()->whereHas('translation', function ($q) {
            $q->where('locale', $this->lang);
        })
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->lang)
            ])
            ->updatedDate($this->updatedDate)
            ->filter($array)
            ->when(isset($active), function ($q) use ($active) {
                $q->whereActive($active);
            })->get();
    }


    public function productsPaginate($perPage, $active = null, $array = [])
    {
        if (isset($array['category_id'])) {

            /** @var Category $category */

            $category = Category::find($array['category_id']);

            $childrenIds = [];

            $grandchildIds = [];
            if ($category && $category->grandchildren->isNotEmpty()) {

                foreach ($category->grandchildren as $children) {

                    $childrenIds[] = $children->id;

                    if ($category && $children->grandchildren->isNotEmpty()) {

                        foreach ($children->grandchildren as $grandchild) {
                            $grandchildIds[] = $grandchild->id;
                        }

                    }

                }

                $array['category_id'] = array_merge($childrenIds, $grandchildIds);
            }
        }
        return $this->model()->filter($array)->updatedDate($this->updatedDate)
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang);
            })
            ->withAvg('reviews', 'rating')
            ->with([
                'stocks' => fn($q) => $q->where('quantity', '>', 0)->where('price', '>', 0),
                'stocks.stockExtras.group.translation' => fn($q) => $q->actualTranslation($this->lang),
                'stocks.discount',
                'stocks.countable',
                'translation' => fn($q) => $q->where('locale', $this->lang)
                    ->select('id', 'product_id', 'locale', 'title'),
                'category' => fn($q) => $q->select('id', 'uuid'),
                'category.translation' => fn($q) => $q->where('locale', $this->lang)
                    ->select('id', 'category_id', 'locale', 'title'),
                'brand' => fn($q) => $q->select('id', 'uuid', 'title'),
                'unit.translation' => fn($q) => $q->where('locale', $this->lang),
            ])
            ->when(isset($array['extrasIds']), function ($q) use ($array) {
                $q->whereHas('stocks.extras', function ($extras) use ($array) {
                    $extras->whereIn('extra_value_id', $array['extrasIds']);
                });
            })
            ->when(isset($array['rest']), function ($q) {
                $q->whereHas('stocks', function ($item) {
                    $item->where('quantity', '>', 0)->where('price', '>', 0);
                })->whereHas('category');
            })
            ->when(isset($array['search']), function ($q) use ($array) {
                $q->whereHas('translations', function ($q) use ($array) {
                    $q->where('locale', $this->lang)->where('title', 'LIKE', '%' . $array['search'] . '%')
                        ->select('id', 'product_id', 'locale', 'title');
                });
            })
            ->when(isset($array['rest']),function ($q){
                $q->whereHas('shop', function ($item) {
                    $item->whereNull('deleted_at')->where('status', 'approved');
                })->where('status',Product::PUBLISHED);
            })
            ->whereHas('shop',function ($q){
                $q->whereNull('deleted_at');
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active)->where('status',Product::PUBLISHED);
            })
            ->when(isset($array['look']), function ($q){
                $q->where('active',true)->where('status',Product::PUBLISHED);
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function outOfStock(int $perPage = 15, int $shopId = null)
    {
        return $this->model()
            ->with([
                'stocks' => fn($b) => $b->where('quantity', 0),
                'translation' => fn($q) => $q->where('locale', $this->lang)
                    ->select('id', 'product_id', 'locale', 'title'),
                'unit.translation' => fn($q) => $q->where('locale', $this->lang),
            ])
            ->whereHas('stocks', function ($b) {
                $b->where('quantity', 0);
            })
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang);
            })
            ->whereHas('shop', function ($item) {
                $item->whereNull('deleted_at')->where('status', 'approved');
            })
            ->when($shopId, function ($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function productDetails(int $id)
    {
        return $this->model()
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang);
            })
            ->withAvg('reviews', 'rating')
            ->with([
                'galleries' => fn($q) => $q->select('id', 'type', 'loadable_id', 'path', 'title'),
                'stocks.stockExtras.group.translation' => fn($q) => $q->where('locale', $this->lang),
                'stocks.discount',
                'translation' => fn($q) => $q->where('locale', $this->lang)
                    ->select('id', 'product_id', 'locale', 'title'),
                'category.translation' => fn($q) => $q->where('locale', $this->lang)
                    ->select('id', 'category_id', 'locale', 'title'),
                'brand' => fn($q) => $q->select('id', 'uuid', 'title'),
                'unit.translation' => fn($q) => $q->where('locale', $this->lang),
                'extras.translation' => fn($q) => $q->where('locale', $this->lang),
            ])->find($id);
    }

    public function productByUUID(string $uuid)
    {
        return $this->model()
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang);
            })
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->with([
                'galleries' => fn($q) => $q->select('id', 'type', 'loadable_id', 'path', 'title'),
                'properties' => fn($q) => $q->where('locale', $this->lang),
                'stocks.stockExtras.group.translation' => fn($q) => $q->where('locale', $this->lang),
                'stocks.discount',
                'stocks.countable',
                'shop.translation' => fn($q) => $q->where('locale', $this->lang),
                'category' => fn($q) => $q->select('id', 'uuid'),
                'category.translation' => fn($q) => $q->where('locale', $this->lang)
                    ->select('id', 'category_id', 'locale', 'title'),
                'brand' => fn($q) => $q->select('id', 'uuid', 'title'),
                'unit.translation' => fn($q) => $q->where('locale', $this->lang),
                'reviews.galleries',
                'reviews.user',
                'translation' => fn($q) => $q->where('locale', $this->lang)
            ])
            ->when(isset($array['rest']), function ($q) {
                $q->whereHas('stocks', function ($item) {
                    $item->where('quantity', '>', 0)->where('price', '>', 0);
                })->whereHas('shop', function ($item) {
                    $item->whereNull('deleted_at')->where('status', 'approved');
                });
            })
            ->firstWhere('uuid', $uuid);
    }

    public function productsByIDs(array $ids)
    {
        return $this->model()->with([
            'stocks.stockExtras.group.translation' => fn($q) => $q->where('locale', $this->lang),
            'stocks.discount',
            'translation' => fn($q) => $q->where('locale', $this->lang)
                ->select('id', 'product_id', 'locale', 'title'),
        ])->whereHas('stocks', function ($item) {
            $item->where('quantity', '>', 0);
        })
            ->whereHas('translation', fn($q) => $q->where('locale', $this->lang))
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->whereHas('shop', function ($item) {
                $item->whereNull('deleted_at')->where('status', 'approved');
            })
            ->whereHas('category')
            ->where('active',1)
            ->where('status',Product::PUBLISHED)
            ->orderBy($array['column'] ?? 'id', $array['sort'] ?? 'desc')
            ->find($ids);
    }

    public function productFilter($array = [])
    {
        $categoryIds = [];

        if (isset($array['categoryIds'])) {
            $categoryIds = Category::with('children')
                ->whereIn('id', $array['categoryIds'])
                ->pluck('id');
        }

        if (isset($array['parent_category_id']) && !isset($array['categoryIds'])) {
            $categoryIds = Category::with('children')
                ->where('parent_id', $array['parent_category_id'])
                ->pluck('id');
        }
        // $array['range'][0] is price from
        // $array['range'][1] is price to
        if (isset($array['range'][0])) {
            $array['range'][0] /= $this->currency();
        }

        if (isset($array['range'][1])) {
            $array['range'][1] /= $this->currency();
        }

        return $this->model()->filter($array)->updatedDate($this->updatedDate)
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang)->select('id', 'product_id', 'locale', 'title');
            })
            ->whereHas('category',function ($q){
                $q->where('active',1);
            })
            ->where('status',Product::PUBLISHED)
            ->withAvg('reviews', 'rating')
            ->with([
                'translation' => function ($q) {
                    $q->where('locale', $this->lang)->select('id', 'product_id', 'locale', 'title');
                },
                'stocks' => fn($q) => $q->where('quantity', '>', 0)->where('price', '>', 0),
                'stocks.countable',
                'stocks.discount',
                'unit:id',
                'unit.translation' => fn($q) => $q->where('locale', $this->lang)->select('id', 'unit_id', 'locale', 'title'),
            ])
            ->whereHas('stocks', function ($q) {
                $q->where('quantity', '>', 0)->where('price', '>', 0);
            })
            ->when(isset($array['extrasIds']), function ($q) use ($array) {
                $q->whereHas('stocks.extras', function ($extras) use ($array) {
                    $extras->whereIn('extra_value_id', $array['extrasIds']);
                });
            })
            ->whereHas('shop', function ($item) {
                $item->where('status', 'approved');
            })
            ->when(isset($categoryIds) && !empty($categoryIds), function ($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds);
            })->when(isset($array['brandIds']), function ($q) use ($array) {
                $q->whereIn('brand_id', $array['brandIds']);
            })->when(isset($array['shopIds']), function ($q) use ($array) {
                $q->whereIn('shop_id', $array['shopIds']);
            })->when(isset($array['category_id']), function ($q) use ($array) {
                $q->where('category_id', $array['category_id']);
            })
            ->when(isset($array['sortByAsc']), function ($q) use ($array) {
                $q->withAvg('stocks', 'price')->orderBy('stocks_avg_price', 'asc');
            })
            ->when(isset($array['sortByDesc']), function ($q) use ($array) {
                $q->withAvg('stocks', 'price')->orderBy('stocks_avg_price', 'desc');
            })
            ->where('active', 1)
            ->paginate($array['perPage'] ?? 15);
    }

    public function productsSearch(string $search, $active = null, $shop = null)
    {
        return $this->model()->with([
            'stocks' => fn($q) => $q->where('quantity', '>', 0)->where('price', '>', 0),
            'stocks.stockExtras.group.translation' => fn($q) => $q->actualTranslation($this->lang),
            'translation' => fn($q) => $q->actualTranslation($this->lang),
        ])
            ->when(isset($array['rest']), function ($q) {
                $q->whereHas('stocks', function ($item) {
                    $item->where('quantity', '>', 0)->where('price', '>', 0);
                });
            })
            ->whereHas('shop', function ($item) {
                $item->whereNull('deleted_at')->where('status', 'approved')->where('open',1);
            })
            ->whereHas('stocks', function ($item) {
                $item->where('quantity', '>', 0)->where('price', '>', 0);
            })
            ->where(function ($query) use ($search) {
                $query->where('keywords', 'LIKE', '%' . $search . '%');
            })
            ->whereHas('translations', function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->select('id', 'product_id', 'locale', 'title');
            })
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->whereActive($active)->where('status',Product::PUBLISHED);
            })
            ->latest()->take(10)->get();
    }

    public function reportPaginate($perPage)
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();
        $perPage = request('export') === 'excel' ? null : $perPage;


        $data = $this->reportPaginateQuery($dateFrom, $dateTo, $perPage);
//        $data = Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), 1,
//            function () use ($dateFrom, $dateTo, $perPage) {
//                return $this->reportPaginateQuery($dateFrom, $dateTo, $perPage);
//            });
        //$data = $this->reportPaginateQuery($dateFrom, $dateTo, $perPage);

        if (request('export') === 'excel') {
            $name = 'products-report-' . Str::random(8);
//            Excel::store(new ProductsReportExport($data), "export/$name.xlsx", 'public');
            ExportJob::dispatchAfterResponse("export/$name.xlsx", $data, ProductsReportExport::class);

            return [
                'path' => 'public/export',
                'file_name' => "export/$name.xlsx",
                'link' => URL::to("storage/export/$name.xlsx"),
            ];
        }

        return $data;
    }

    public function reportPaginateQuery($dateFrom, $dateTo, ?int $perPage = 15)
    {
        $search = (string)request('search');
        return Product::query()
            ->select([
                'products.id',
                'products.uuid',
                'products.category_id',
                'products.shop_id',
                'products.bar_code',
                'pt.title as translation_title',
                'sht.title as shop_translation_title',
                'seller.firstname as seller_firstname',
                'seller.lastname as seller_lastname',
                'seller.id as seller_id',
                'products.deleted_at'])
            ->addSelect([
                'orders_count' => Order::whereHas('orderDetails', function ($detail) {
                    $detail
                        ->when(request('shops'), fn($q) => $q->whereHas('products.stock.countable',
                            fn($p) => $p->whereIn('shop_id', request('shops'))))
                        ->when(request('sellers'), function (Builder $query) {
                            $query->whereHas('products.stock.countable.shop',
                                fn($q) => $q->whereIn('user_id', request('sellers')));
                        })
                        ->whereHas('products.stock', function ($stock) {
                            $stock->where('countable_type', Product::class)
                                ->whereHas('countable')
                                ->whereColumn('countable_id', 'products.id');
                        });
                })
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('status', Order::DELIVERED)
                    ->selectRaw('IFNULL(COUNT(id), 0)'),

                'net_sales' => OrderProduct::whereHas('stock', function ($stock) {
                    $stock->where('countable_type', Product::class)
                        ->whereColumn('countable_id', 'products.id')
                        ->whereHas('countable', function ($product) {
                            $product->whereActive(1)
                                ->when(request('shops'), function ($query) {
                                    $query->whereIn('products.shop_id', request('shops'));
                                })
                                ->when(request('sellers'), function (Builder $query) {
                                    $query->whereHas('shop',
                                        fn($q) => $q->whereIn('user_id', request('sellers')));
                                });
                        });
                })->whereHas('detail.order', function ($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                        ->where('orders.status', OrderDetail::DELIVERED);
                })
                    ->netSalesSum(),

                'items_sold' => OrderProduct::whereHas('stock', function ($stock) {
                    $stock->where('countable_type', Product::class)
                        ->whereColumn('countable_id', 'products.id')
                        ->whereHas('countable', function ($product) {
                            $product->whereActive(1)
                                ->when(request('shops'), function ($query) {
                                    $query->whereIn('products.shop_id', request('shops'));
                                })
                                ->when(request('sellers'), function (Builder $query) {
                                    $query->whereHas('shop',
                                        fn($q) => $q->whereIn('user_id', request('sellers')));
                                });
                        });
                })->whereHas('detail.order', function ($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                        ->where('orders.status', OrderDetail::DELIVERED);
                })
                    ->selectRaw('IFNULL(SUM(quantity), 0)'),

                'stocks_total' => Stock::where('countable_type', Product::class)
                    ->whereColumn('countable_id', 'products.id')
                    ->whereHas('countable', function ($product) {
                        $product->whereActive(1)
                            ->when(request('shops'), function ($query) {
                                $query->whereIn('products.shop_id', request('shops'));
                            })
                            ->when(request('sellers'), function (Builder $query) {
                                $query->whereHas('shop',
                                    fn($q) => $q->whereIn('user_id', request('sellers')));
                            });
                    })
                    ->whereHas('orderProducts.detail', function ($detail) use ($dateFrom, $dateTo) {
                        $detail
                            ->whereHas('order', fn($q) => $q->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                                ->where('status', OrderDetail::DELIVERED));
                    })
                    ->selectRaw('IFNULL(SUM(quantity), 0) as stocks_total'),

                'status' => Stock::where('countable_type', Product::class)
                    ->whereColumn('countable_id', 'products.id')
                    ->whereHas('countable', function ($product) {
                        $product->whereActive(1)
                            ->when(request('shops'), function ($query) {
                                $query->whereIn('products.shop_id', request('shops'));
                            })
                            ->when(request('sellers'), function (Builder $query) {
                                $query->whereHas('shop',
                                    fn($q) => $q->whereIn('user_id', request('sellers')));
                            });
                    })
                    ->whereHas('orderProducts.detail', function ($detail) use ($dateFrom, $dateTo) {
                        $detail->whereHas('order',
                            fn($q) => $q->whereBetween('orders.created_at', [$dateFrom, $dateTo])->where('status', Order::DELIVERED));
                    })
                    ->selectRaw("CASE WHEN IFNULL(SUM(quantity), 0)<=0 THEN 'Out of stock' WHEN (IFNULL(SUM(quantity), 0)>0 and IFNULL(SUM(quantity), 0)<=5) THEN 'Low stock' ELSE 'In stock' END AS 'status'"),
            ])
            ->whereHas('stocksWithTrashed.orderProducts.detail', function ($detail) use ($dateFrom, $dateTo) {
                $detail
                    ->whereHas('order', fn($q) => $q
                        ->where('orders.created_at', '>=', $dateFrom)
                        ->where('orders.created_at', '<=', $dateTo)
                        ->where('orders.status', Order::DELIVERED));
            })
            ->join('product_translations as pt', function ($join) {
                $join->on('products.id', '=', 'pt.product_id')
                    ->where('pt.locale', app()->getLocale());
            })
            ->join('shops as sh', function (JoinClause $join) {
                $join->on('sh.id', '=', 'products.shop_id')
                    ->leftJoin('shop_translations as sht', function ($join) {
                        $join->on('sh.id', '=', 'sht.shop_id')
                            ->where('sht.locale', app()->getLocale());
                    })
                    ->join('users as seller', 'sh.user_id', '=', 'seller.id');
            })
            ->when($search, function ($query) use ($search) {
                $search = rtrim($search, " \t.");
                $query->where(function ($q) use ($search) {
                    $q->where('products.keywords', 'LIKE', "%$search%")
                        ->orWhere('pt.title', 'LIKE', "%$search%")
                        ->orWhere('sht.title', 'LIKE', "%$search%");
                });
            })
            ->when(request('products'), function ($query) {
                $query->whereIn('products.id', request('products'));
            })
            ->when(request('shops'), function ($query) {
                $query->whereIn('products.shop_id', request('shops'));
            })
            ->when(request('sellers'), function (Builder $query) {
                $query->whereHas('shop',
                    fn($q) => $q->whereIn('user_id', request('sellers')));
            })
            ->when(request('categories'), function ($query) {
                $query->whereIn('products.category_id', request('categories'));
            })
            ->with(['category' => function ($category) {
                $category->join('category_translations as cat_t', function ($join) {
                    $join->on('categories.id', '=', 'cat_t.category_id')
                        ->where('cat_t.locale', app()->getLocale());
                })->select(['categories.id',
                    'categories.uuid',
                    'categories.keywords',
                    'categories.parent_id',
                    'cat_t.title as translation_title'])
                    ->with('parenRecursive');
            }])
            ->withCount(['stocks as variations'])
            ->orderBy(request('column', 'id'), request('sort', 'desc'))
            ->where('products.active', 1)
            ->withTrashed()
            ->when($perPage,
                fn($q) => $q->paginate($perPage),
                fn($q) => $q->get());
    }

    public function productReportChartCache()
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();
        //return $this->reportChart($dateFrom, $dateTo);
        return $this->reportChart($dateFrom, $dateTo);

//        return Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), $ttl,
//            function () use ($dateFrom, $dateTo) {
//                return $this->reportChart($dateFrom, $dateTo);
//            });
    }

    public function reportChart($dateFrom, $dateTo)
    {
        $itemsSold = moneyFormatter($this->itemsSoldQuery($dateFrom, $dateTo)->sum('quantity'));
        $netSales = moneyFormatter($this->netSalesQuery($dateFrom, $dateTo)->netSalesSum()->value('net_sales_sum'));
        $ordersCount = moneyFormatter($this->ordersCountQuery($dateFrom, $dateTo)->count());
        $chart = $this->getChartData($dateFrom, $dateTo);

        $defaultCurrency = defaultCurrency();

        return compact('itemsSold', 'netSales', 'ordersCount', 'chart', 'defaultCurrency');
    }

    public function getProductById($id)
    {
        $model = $this->model()
            ->select(['products.id as id', 'products.uuid as uuid', 'pt.title as translation_title'])
            ->where('products.active', 1)
            ->where('products.id', $id)
            ->join('product_translations as pt', function ($join) {
                $join->on('products.id', '=', 'pt.product_id')
                    ->where('pt.locale', app()->getLocale());
            })
            ->withTrashed()
            ->first();

        return CompareResource::make($model);
    }

    public function productReportCompareCache()
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();

//        return Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), $ttl,
//            function () use ($dateFrom, $dateTo) {
                return $this->reportCompare($dateFrom, $dateTo);
//            });
    }

    private function getChartData($dateFrom, $dateTo, $id = null)
    {
        switch (request('chart', 'items_sold')) {
            case 'orders_count':
                return $this->ordersCount($dateFrom, $dateTo, $id);
            case 'net_sales':
                return $this->netSales($dateFrom, $dateTo, $id);
            case 'items_sold':
            default:
                return $this->itemsSold($dateFrom, $dateTo, $id);
        }
    }

    public function reportCompare($dateFrom, $dateTo): array
    {
        $itemsSold = moneyFormatter($this->itemsSoldQuery($dateFrom, $dateTo, request('ids'))->sum('quantity'));
        $netSales = moneyFormatter($this->netSalesQuery($dateFrom, $dateTo, request('ids'))
            ->netSalesSum()
            ->value('net_sales_sum'));
        $ordersCount = moneyFormatter($this->ordersCountQuery($dateFrom, $dateTo, request('ids'))->count());
        $defaultCurrency = defaultCurrency();
        $charts = [];
        foreach (request('ids') as $id) {
            $charts[] = [
                'translation' => $this->getProductById($id),
                'chart' => $this->getChartData($dateFrom, $dateTo, $id),
            ];
        }

        return compact('itemsSold', 'netSales', 'ordersCount', 'defaultCurrency', 'charts');
    }

    public function itemsSold($dateFrom, $dateTo, $id = null)
    {
        return $this->itemsSoldQuery($dateFrom, $dateTo, [$id])
            ->select(
                DB::raw("SUM(quantity) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function itemsSoldQuery($dateFrom, $dateTo, $productIds = null)
    {
        return OrderProduct::whereHas('stock', function ($stock) use ($productIds) {
            $stock
                ->when($productIds && $productIds[0] !== null, fn($q) => $q->whereIn('countable_id', $productIds))
                ->when(request('products'), fn($q) => $q->whereIn('countable_id', request('products')))
                ->whereHas('countable', function ($product) {
                    $product->where('active', 1)
                        ->when(request('categories'), fn($q) => $q->whereIn('category_id', request('categories')))
                        ->when(request('sellers'), function ($query) {
                            $query->whereHas('shop',
                                fn($q) => $q->whereIn('user_id', request('sellers')));
                        })
                        ->when(request('shops'), fn($q) => $q->whereIn('shop_id', request('shops')));
                })
                ->groupBy('countable_id');
        })
            ->whereHas('detail', function ($detail) use ($dateFrom, $dateTo) {
                $detail->whereHas('order',
                    fn($q) => $q->where('orders.status', Order::DELIVERED)->whereBetween('orders.created_at', [$dateFrom, $dateTo]));
            });
    }

    public function netSales($dateFrom, $dateTo, $id = null)
    {
        return $this->netSalesQuery($dateFrom, $dateTo, [$id])
            ->select(
                DB::raw(OrderProduct::NETSALESSUMQUERY . " as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function netSalesQuery($dateFrom, $dateTo, $productIds = null)
    {
        return OrderProduct::whereHas('stock', function ($stock) use ($productIds) {
        $stock->where('countable_type', Product::class)
            ->when($productIds && $productIds[0] !== null,
                fn($q) => $q->whereIn('countable_id', $productIds))
            ->when(request('products'), fn($q) => $q->whereIn('countable_id', request('products')))
            ->whereHas('countable', function ($product) use ($productIds) {
                $product->whereActive(1)
                    ->when($productIds && $productIds[0] !== null,
                        fn($q) => $q->whereIn('id', $productIds))
                    ->when(request('shops'), function ($query) {
                        $query->whereIn('products.shop_id', request('shops'));
                    })
                    ->when(request('categories'), fn($q) => $q->whereIn('category_id', request('categories')))
                    ->when(request('sellers'), function (Builder $query) {
                        $query->whereHas('shop',
                            fn($q) => $q->whereIn('user_id', request('sellers')));
                    });
            });
    })->whereHas('detail.order', function ($q) use ($dateFrom, $dateTo) {
        $q->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->where('orders.status', OrderDetail::DELIVERED);
    })
        ->netSalesSum();
    }

    public function ordersCount($dateFrom, $dateTo, $id = null)
    {
        return $this->ordersCountQuery($dateFrom, $dateTo, [$id])
            ->select(
                DB::raw("COUNT(id) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function ordersCountQuery($dateFrom, $dateTo, array $productIds = null)
    {
        return Order::whereHas('orderDetails', function ($detail) use ($productIds, $dateFrom, $dateTo) {
            $detail
                ->whereHas('products.stock', function ($stock) use ($productIds) {
                    $stock->where('countable_type', Product::class)
                        ->when($productIds && $productIds[0] !== null,
                            fn($q) => $q->whereIn('countable_id', $productIds))
                        ->when(request('products'),
                            fn($q) => $q->whereIn('countable_id', request('products')))
                        ->whereHas('countable', function ($product) {
                            $product->where('active', 1)
                                ->when(request('categories'), fn($q
                                ) => $q->whereIn('category_id', request('categories')))
                                ->when(request('sellers'), function ($query) {
                                    $query->whereHas('shop',
                                        fn($q) => $q->whereIn('user_id', request('sellers')));
                                })
                                ->when(request('shops'), fn($q) => $q->whereIn('shop_id', request('shops')));
                        })
                        ->groupBy('countable_id');
                });
        })
            ->where('orders.status', Order::DELIVERED)
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo]);
    }

    public function isPossibleCacheProductExtrasReport($product)
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();

        return Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), $ttl,//day
            function () use ($product, $dateFrom, $dateTo) {
                return $this->productExtrasReport($product, $dateFrom, $dateTo);
            });
    }

    public function productExtrasReport($product, $dateFrom, $dateTo)
    {
        $product = Product::whereActive(1)->withTrashed()->findOrFail($product);

        return $product->extras()
            ->addSelect([
                'orders_count' => Order::whereHas('orderDetails', function ($detail) use ($product) {
                    $detail->whereHas('products.stock', function (Builder $stock) use ($product) {
                        $stock->whereHas('stockExtras', function (Builder $stockExtras) {
                            $stockExtras->where('extra_values.extra_group_id', 'extra_groups.id');
                        })->where('stocks.countable_id', $product->id);
                    });
                })->whereBetween('created_at', [$dateFrom, $dateTo])->where('status', Order::DELIVERED)
                    ->selectRaw('IFNULL(COUNT(id), 0)'),
                'net_sales' => Order::whereHas('orderDetails', function ($detail) use ($product) {
                    $detail->whereHas('products', function ($orderProducts) use ($product) {
                        $orderProducts
                            ->whereHas('stock', function ($stock) use ($product) {
                                $stock->whereHas('stockExtras', function (Builder $stockExtras) {
                                    $stockExtras->where('extra_values.extra_group_id', 'extra_groups.id');
                                })->where('stocks.countable_id', $product->id);
                            });
                    });
                })->whereBetween('created_at', [$dateFrom, $dateTo])->where('status', Order::DELIVERED)
                    ->selectRaw('IFNULL( TRUNCATE( CAST( SUM(price) as decimal(7,2)) ,2) ,0)'),
                'items_sold' => OrderProduct::whereHas('detail', function ($detail) use (
                    $dateFrom,
                    $dateTo
                ) {
                    $detail
                        ->whereHas('order', fn(builder $q) => $q->whereBetween('created_at', [$dateFrom, $dateTo])
                            ->where('status', Order::DELIVERED));
                })->whereHas('stock', function ($stock) use ($product) {
                    $stock->whereHas('stockExtras', function (Builder $stockExtras) {
                        $stockExtras->where('extra_values.extra_group_id', 'extra_groups.id');
                    })->where('stocks.countable_id', $product->id);
                })
                    ->selectRaw('CAST(IFNULL(SUM(quantity), 0) AS SIGNED)'),
            ])
            ->with([
                //'countable:id,uuid,active,category_id,bar_code',
                //'countable.translation:id,product_id,locale,title',
                'translation',
                //'translation' => fn($q) => $q->actualTranslation($this->lang),
            ])
            ->get();
    }
}

