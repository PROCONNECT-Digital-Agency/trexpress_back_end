<?php

namespace App\Repositories\OrderRepository;

use App\Repositories\Interfaces\OverviewReportRepoInterface;
use App\Models\{Category, Order, OrderDetail, OrderProduct, Product, Shop, User};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class OverviewReportReportRepository extends RevenueReportReportRepository implements OverviewReportRepoInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    public function leaderboards($limit)
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();

//        return Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), $ttl,
//            function () use ($dateFrom, $dateTo, $limit) {
                return $this->leaderboardsData($dateFrom, $dateTo, $limit);
//            });
    }

    public function leaderboardsData($dateFrom, $dateTo, int $limit)
    {
        $leaderboardsData = [];
        if (in_array('categories', request('leaderboards', []))) {
            $leaderboardsData['categories'] = Category::select(
                ['categories.id',
                    'categories.uuid',
                    'categories.parent_id',
                    'cat_t.title as translation_title'])
                ->addSelect([
                    'items_sold' => OrderProduct::whereHas('stock', function (Builder $stock) {
                        $stock->where('countable_type', Product::class)
                            ->whereHas('countable', function (Builder $product) {
                                $product->whereColumn('category_id', 'categories.id')
                                    ->when(request('shops'), function (Builder $query) {
                                        $query->whereIn('shop_id', request('shops'));
                                    })
                                    ->when(request('sellers'), function (Builder $query) {
                                        $query->whereHas('shop', function ($query) {
                                            $query->whereIn('user_id', request('sellers'));
                                        });
                                    });
                            });
                    })
                        ->whereHas('detail', function ($detail) use ($dateFrom, $dateTo) {
                            $detail
                                ->whereHas('order', function (Builder $order) use ($dateFrom, $dateTo) {
                                    $order->where('status',OrderDetail::DELIVERED)
                                        ->whereDate('created_at', '>=', $dateFrom)
                                        ->whereDate('created_at', '<=', $dateTo);
                                });
                        })
                        ->selectRaw('IFNULL(sum(quantity), 0)  as sum_quantity'),
                    'net_sales'  => OrderDetail::query()
                        ->whereHas('products.stock',
                            function ($stock) {
                                $stock->where('countable_type', Product::class)
                                    ->whereHas('countable', function (Builder $product) {
                                        $product->whereColumn('category_id', 'categories.id')
                                            ->when(request('shops'), function (Builder $query) {
                                                $query->whereIn('shop_id', request('shops'));
                                            })
                                            ->when(request('sellers'), function (Builder $query) {
                                                $query->whereHas('shop', function ($query) {
                                                    $query->whereIn('user_id', request('sellers'));
                                                });
                                            });
                                    });
                            })
                        ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo])
                            ->where('status',OrderDetail::DELIVERED))
                        ->netSalesSum(),
                ])
                ->whereActive(1)
                ->join('category_translations as cat_t', function ($join) {
                    $join->on('categories.id', '=', 'cat_t.category_id')
                        ->where('cat_t.locale', app()->getLocale());
                })
                ->when(request('shops'), function (Builder $query) {
                    $query->whereHas('products', function ($query) {
                        $query->whereIn('shop_id', request('shops'));
                    });
                })
                ->when(request('sellers'), function (Builder $query) {
                    $query->whereHas('products.shop', function ($query) {
                        $query->whereIn('user_id', request('sellers'));
                    });
                })
                ->whereHas('products.stocksWithTrashed', function ($stock) use ($dateFrom, $dateTo) {
                    $stock->where('countable_type', Product::class)
                        ->whereHas('orderProducts.detail', function ($detail) use ($dateFrom, $dateTo) {
                            $detail
                                ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo])
                                    ->where('status',OrderDetail::DELIVERED));
                        });
                })
                ->limit($limit)
                ->orderBy(request('column.categories', 'items_sold'), request('sort.categories', 'DESC'))
                ->get();
        }
        if (in_array('products', request('leaderboards', []))) {
            $leaderboardsData['products'] = Product::query()
                ->select(['products.id',
                    'products.uuid',
                    'products.active',
                    'products.deleted_at',
                    'pt.title as translation_title'])
                ->addSelect([
                    'net_sales'  => OrderDetail::whereHas('products.stock', function ($stock) {
                            $stock->where('countable_type', Product::class)
                                ->whereHas('countable', function ($query) {
                                    $query->whereActive(1)
                                        ->when(request('shops'), function ($query) {
                                            $query->whereIn('shop_id', request('shops'));
                                        })
                                        ->when(request('sellers'), function (Builder $query) {
                                            $query->whereHas('shop',
                                                fn($q) => $q->whereIn('user_id', request('sellers')));
                                        });
                                })
                                ->whereColumn('countable_id', 'products.id');
                        })
                        ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo])
                            ->where('status', OrderDetail::DELIVERED))
                        ->netSalesSum(),
                    'items_sold' => OrderProduct::whereHas('stock', function ($stock) {
                        $stock->where('countable_type', Product::class)
                            ->whereColumn('countable_id', 'products.id')
                            ->whereHas('countable', function ($query) {
                                $query->whereActive(1)
                                    ->when(request('shops'), function ($query) {
                                        $query->whereIn('shop_id', request('shops'));
                                    })
                                    ->when(request('sellers'), function (Builder $query) {
                                        $query->whereHas('shop',
                                            fn($q) => $q->whereIn('user_id', request('sellers')));
                                    });
                            });
                    })
                        ->whereHas('detail', function ($detail) use ($dateFrom, $dateTo) {
                            $detail
                                ->whereHas('order',
                                    fn($q) => $q->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                                        ->where('status', OrderDetail::DELIVERED));
                        })
                        ->selectRaw('IFNULL(SUM(quantity), 0)'),
                ])
                ->when(request('shops'), function ($query) {
                    $query->whereIn('products.shop_id', request('shops'));
                })
                ->when(request('sellers'), function (Builder $query) {
                    $query->whereHas('shop',
                        fn($q) => $q->whereIn('user_id', request('sellers')));
                })
                ->where('active', 1)
                ->whereHas('stocksWithTrashed.orderProducts.detail', function ($detail) use ($dateFrom, $dateTo) {
                    $detail
                        ->whereHas('order', fn($q) => $q->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                            ->where('status', OrderDetail::DELIVERED));
                })
                ->join('product_translations as pt', function ($join) {
                    $join->on('products.id', '=', 'pt.product_id')
                        ->where('pt.locale', app()->getLocale());
                })
                ->where('products.active', 1)
                ->limit($limit)
                ->orderBy(request('column.products', 'items_sold'), request('sort.products', 'DESC'))
                ->withTrashed()
                ->get();
        }
        if (in_array('customers', request('leaderboards', []))) {
            $leaderboardsData['customers'] = User::query()
                ->select(['users.id',
                    'users.uuid',
                    'users.firstname',
                    'users.lastname',
                    DB::raw("CONCAT(users.firstname, ' ', ifnull(users.lastname,'')) as full_name")])
                ->addSelect([
                    'orders_count' => Order::whereColumn('user_id', 'users.id')
                        ->status('completed')
                        ->selectRaw('COUNT(id)'),
                    'total_spend'  => Order::whereColumn('user_id', 'users.id')
                        ->status('completed')
                        ->selectRaw('TRUNCATE(IFNULL(SUM(price),0),2)'),
                ])
                ->when(request('sellers'), function (Builder $query) {
                    $query->whereHas('orders.orderDetails.products.stock.countable.shop',
                        function ($shop) {
                            $shop->whereIn('user_id', request('sellers'));
                        });
                })
                ->when(request('shops'), function (Builder $query) {
                    $query->whereHas('shop',
                        fn($q) => $q->whereIn('id', request('shops')));
                })
                ->orderBy(request('column.customers', 'total_spend'), request('sort.customers', 'DESC'))
                //->when(request('column.customers') === 'full_name',
                //    function ($query) {
                //        $query->orderBy('users.firstname', request('sort.customers', 'ASC'))
                //            ->orderBy('users.lastname', request('sort.customers', 'ASC'));
                //    },
                //    function ($query) {
                //        $query->orderBy(request('column.customers', 'total_spend'), request('sort.customers', 'DESC'));
                //    })
                ->get();
        }
        if (in_array('shops', request('leaderboards', []))) {
            $leaderboardsData['shops'] = Shop::query()
                ->select(['shops.id', 'shops.uuid', 'sht.title as shop_translation_title'])
                ->join('shop_translations as sht', function ($join) {
                    $join->on('shops.id', '=', 'sht.shop_id')
                        ->where('sht.locale', app()->getLocale());
                })
                ->addSelect([
                    'items_sold' => OrderProduct::whereHas('stock.countable',
                        function ($product) {
                            $product->whereColumn('shop_id', 'shops.id');
                        })
                        ->whereHas('detail', function ($detail) use ($dateFrom, $dateTo) {
                            $detail->whereStatus(OrderDetail::DELIVERED)
                                ->whereHas('order',
                                    fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]));
                        })
                        ->selectRaw('IFNULL(TRUNCATE(sum(quantity),2), 0)'),
                    'net_sales'  => OrderDetail::whereHas('products.stock.countable', function ($product) {
                            $product->whereColumn('shop_id', 'shops.id');
                        })
                        ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo])
                            ->where('status', OrderDetail::DELIVERED))
                        ->netSalesSum(),
                ])
                ->when(request('sellers'), function (Builder $query) {
                    $query->whereIn('shops.user_id', request('sellers'));
                })
                ->when(request('shops'), function (Builder $query) {
                    $query->whereIn('shops.id', request('shops'));
                })
                ->orderBy(request('column.shops', 'net_sales'), request('sort.shops', 'DESC'))
                ->get();
        }

        return $leaderboardsData;
    }

    public function reportChartCache()
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();

        return $this->reportChart($dateFrom, $dateTo);

//        return Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), $ttl,
//            function () use ($dateFrom, $dateTo) {
//                return $this->reportChart($dateFrom, $dateTo);
//            });
    }

    public function reportChart($dateFrom, $dateTo)
    {
        $totalPrice           = moneyFormatter($this->orderQueryFormatter($dateFrom, $dateTo, 'completed')
            ->sum('price'));
        $netSalesSum          = $this->netSalesQuery($dateFrom, $dateTo, 'completed')
            ->netSalesSum()
            ->value('net_sales_sum');
        $completedOrdersCount = $this->orderQueryFormatter($dateFrom, $dateTo, 'completed')->count();
        $canceledOrders       = moneyFormatter($this->orderQueryFormatter($dateFrom, $dateTo, 'canceled')
            ->sum('price'));
        $netSalesAvg          = moneyFormatter($netSalesSum / ($completedOrdersCount ? : 1));
        $netSalesSum          = moneyFormatter($netSalesSum);
        $completedOrdersCount = moneyFormatter($completedOrdersCount);
        $productsSold         = moneyFormatter($this->productSoldQuery($dateFrom, $dateTo)->count());
        $taxTotal             = moneyFormatter($this->netSalesQuery($dateFrom, $dateTo, 'completed')->sum('tax'));
        $itemSold             = moneyFormatter($this->itemsSoldQuery($dateFrom, $dateTo, 'completed')->sum('quantity'));
        $totalShippingFree    = moneyFormatter($this->netSalesQuery($dateFrom, $dateTo, 'completed')
            ->sum('total_delivery_fee'));
        $defaultCurrency      = defaultCurrency();

        $charts        = [];
        $requestCharts = request('charts', []);
        $completed     = 'completed';
        $canceled      = 'canceled';

        if (in_array('total_price', $requestCharts)) {
            $charts['total_price'] = $this->totalPriceGroupByTime($dateFrom, $dateTo, $completed);
        }
        if (in_array('completed_orders_count', $requestCharts)) {
            $charts['completed_orders_count'] = $this->orderCountGroupByTime($dateFrom, $dateTo, $completed);
        }
        if (in_array('canceled_orders', $requestCharts)) {
            $charts['canceled_orders'] = $this->totalPriceGroupByTime($dateFrom, $dateTo, $canceled);
        }
        if (in_array('net_sales', $requestCharts)) {
            $charts['net_sales'] = $this->netSalesSumGroupByTime($dateFrom, $dateTo, $completed);
        }
        if (in_array('tax_total', $requestCharts)) {
            $charts['tax_total'] = $this->taxTotalGroupByTime($dateFrom, $dateTo, $completed);
        }
        if (in_array('items_sold', $requestCharts)) {
            $charts['items_sold'] = $this->itemsSoldGroupByTime($dateFrom, $dateTo, $completed);
        }
        if (in_array('total_shopping_free', $requestCharts)) {
            $charts['total_shopping_free'] = $this->deliveryFreeSumGroupByTime($dateFrom, $dateTo, $completed);
        }

        return compact('totalPrice',
            'netSalesSum',
            'completedOrdersCount',
            'canceledOrders',
            'netSalesAvg',
            'productsSold',
            'taxTotal',
            'itemSold',
            'totalShippingFree',
            'defaultCurrency',
            'charts');
    }

    protected function productSoldQuery($from, $to, string $status = null): Builder
    {
        return Product::query()
            ->whereHas('stocksWithTrashed.orderProducts.detail.order',
                function (Builder $order) use ($from, $to, $status) {
                    $order->whereBetween('created_at', [$from, $to])
                        ->status($status ?? request('order_status', 'completed'));
                })
            ->when(request('shops'), function ($query) {
                $query->whereIn('shop_id', request('shops'));
            })
            ->withTrashed();
    }
}
