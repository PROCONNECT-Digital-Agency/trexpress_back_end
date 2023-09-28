<?php

namespace App\Repositories\ProductRepository;

use App\Exports\StockReportExport;
use App\Exports\VariationsReportExport;
use App\Http\Resources\CompareResource;
use App\Jobs\ExportJob;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Stock;
use App\Repositories\CoreRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

final class StockRepository extends CoreRepository
{
    private string $lang;

    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }

    protected function getModelClass()
    {
        return Stock::class;
    }

    public function productStockReportCache($product)
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();
        return $this->productStockReport($product, $dateFrom, $dateTo);

//        return Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), $ttl,
//            function () use ($product, $dateFrom, $dateTo) {
//            });
    }

    public function productStockReport($product, $dateFrom, $dateTo)
    {
        $product = Product::whereActive(1)->withTrashed()->findOrFail($product);

        return $product->stocksWithTrashed()
            ->select(['stocks.id',
                'stocks.quantity',
                'stocks.deleted_at',
                'status' => DB::raw("CASE WHEN stocks.quantity<=0 THEN 'Out of stock' WHEN (stocks.quantity>0 and stocks.quantity<=5) THEN 'Low stock' ELSE 'In stock' END AS 'status'")])
            ->addSelect([
                'orders_count' => Order::whereHas('orderDetails', function ($detail) use ($product) {
                    $detail->whereHas('products', function ($orderProducts) use ($product) {
                        $orderProducts->whereColumn('stock_id', 'stocks.id')
                            ->whereHas('stock', function ($stock) use ($product) {
                                $stock->where('countable_id', $product->id);
                            });
                    });
                })->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('status', Order::DELIVERED)
                    ->selectRaw('IFNULL(COUNT(id), 0)'),

                'net_sales' => Order::whereHas('orderDetails', function ($detail) use ($product) {
                    $detail->whereHas('products', function ($orderProducts) use ($product) {
                        $orderProducts->whereColumn('stock_id', 'stocks.id')
                            ->whereHas('stock', function ($stock) use ($product) {
                                $stock->where('countable_id', $product->id);
                            });
                    });
                })->whereBetween('orders.created_at', [$dateFrom, $dateTo])->where('status', OrderDetail::DELIVERED)
                    ->selectRaw('IFNULL( TRUNCATE( CAST( SUM(price) as decimal(7,2)) ,2) ,0)'),
                'items_sold' => OrderProduct::whereColumn('stock_id', 'stocks.id')
                    ->whereHas('detail', function ($detail) use ($dateFrom, $dateTo) {
                        $detail->whereHas('order',
                            fn($q) => $q->whereBetween('orders.created_at', [$dateFrom, $dateTo])->where('orders.status', OrderDetail::DELIVERED));
                    })->whereHas('stock', function ($stock) use ($product) {
                        $stock->where('countable_id', $product->id);
                    })
                    ->selectRaw('CAST(IFNULL(SUM(quantity), 0) AS SIGNED)'),
            ])
            ->where('countable_type', Product::class)
            ->with([
                //'countable:id,uuid,active,category_id,bar_code',
                //'countable.translation:id,product_id,locale,title',
                'stockExtras.group.translation' => fn($q) => $q->actualTranslation($this->lang),
            ])
            ->get();
    }

    public function variationsReportPaginate($perPage)
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();
        $perPage = request('export') === 'excel' ? null : $perPage;

        $data = Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), 86400,//day
            function () use ($dateFrom, $dateTo, $perPage) {
                return $this->variationsReportPaginateQuery($dateFrom, $dateTo, $perPage);
            });


        if (request('export') === 'excel') {
            $name = 'variations-report-' . Str::random(8);
//            Excel::store(new ProductsReportExport($data), "export/$name.xlsx", 'public');
            ExportJob::dispatchAfterResponse("export/$name.xlsx", $data, VariationsReportExport::class);

            return [
                'path' => 'public/export',
                'file_name' => "export/$name.xlsx",
                'link' => URL::to("storage/export/$name.xlsx"),
            ];
        }

        return $data;
    }

    public function variationsReportPaginateQuery($dateFrom, $dateTo, $perPage)
    {
        return Stock::query()
            ->select(['stocks.id',
                'stocks.quantity',
                'stocks.deleted_at',
                'products.id as product_id',
                'products.bar_code as bar_code',
                'pt.title as translation_title',
                'products.shop_id as shop_id',
                'sht.title as shop_translation_title',
                'seller.firstname as seller_firstname',
                'seller.lastname as seller_lastname',
                'seller.id as seller_id',
                'status' => DB::raw("CASE WHEN stocks.quantity<=0 THEN 'Out of stock' WHEN (stocks.quantity>0 and stocks.quantity<=5) THEN 'Low stock' ELSE 'In stock' END AS 'status'")])
            ->addSelect([
                'orders_count' => Order::whereHas('orderDetails', function ($detail) {
                    $detail
                        //->whereStatus(OrderDetail::DELIVERED)
                        ->whereHas('products', function ($orderProducts) {
                            $orderProducts->whereColumn('stock_id', 'stocks.id')
                                ->when(request('shops'), function (Builder $query) {
                                    $query->whereHas('stock.countable',
                                        fn($q) => $q->whereIn('shop_id', request('shops')));
                                })
                                ->when(request('sellers'), function (Builder $query) {
                                    $query->whereHas('stock.countable.shop',
                                        fn($q) => $q->whereIn('user_id', request('sellers')));
                                });
                        });
                })
                    ->where('orders.status', Order::DELIVERED)
                    ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                    ->selectRaw('IFNULL(COUNT(id), 0)'),

                'net_sales' => OrderDetail::whereHas('products', function ($orderProducts) {
                    $orderProducts->whereColumn('stock_id', 'stocks.id')
                        ->when(request('shops'), function (Builder $query) {
                            $query->whereHas('stock.countable',
                                fn($q) => $q->whereIn('shop_id', request('shops')));
                        })
                        ->when(request('sellers'), function (Builder $query) {
                            $query->whereHas('stock.countable.shop',
                                fn($q) => $q->whereIn('user_id', request('sellers')));
                        });
                })
                    ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo])->where('status', Order::DELIVERED))
                    ->netSalesSum(),

                'items_sold' => OrderProduct::whereColumn('stock_id', 'stocks.id')
                    ->when(request('shops'), function (Builder $query) {
                        $query->whereHas('stock.countable',
                            fn($q) => $q->whereIn('shop_id', request('shops')));
                    })
                    ->when(request('sellers'), function (Builder $query) {
                        $query->whereHas('stock.countable.shop',
                            fn($q) => $q->whereIn('user_id', request('sellers')));
                    })
                    ->whereHas('detail', function ($detail) use ($dateFrom, $dateTo) {
                        $detail->whereHas('order',
                            fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo])
                                ->where('status', Order::DELIVERED));
                    })
                    ->selectRaw('IFNULL(TRUNCATE(sum(quantity),2), 0)'),
            ])
            ->where('countable_type', Product::class)
            ->whereHas('orderProducts', function ($orderProduct) use ($dateFrom, $dateTo) {
                $orderProduct->whereHas('detail', function ($detail) use ($dateFrom, $dateTo) {
                    $detail->whereHas('order', function ($order) use ($dateFrom, $dateTo) {
                        $order->whereBetween('created_at', [$dateFrom, $dateTo])->where('status', Order::DELIVERED);
                    });
                });
            })
            ->join('products', function (JoinClause $join) {
                $join->on('products.id', '=', 'stocks.countable_id')
                    ->join('product_translations as pt', function (JoinClause $join) {
                        $join->on('products.id', '=', 'pt.product_id')
                            ->where('pt.locale', app()->getLocale());
                    });
            })
            ->join('shops as sh', function (JoinClause $join) {
                $join->on('sh.id', '=', 'products.shop_id')
                    ->leftJoin('shop_translations as sht', function ($join) {
                        $join->on('sh.id', '=', 'sht.shop_id')
                            ->where('sht.locale', app()->getLocale());
                    })
                    ->join('users as seller', 'sh.user_id', '=', 'seller.id');
            })
            ->when(request('shops'), function ($query) {
                $query->whereIn('products.shop_id', request('shops'));
            })
            ->when(request('sellers'), function (Builder $query) {
                $query->whereIn('sh.user_id', request('sellers'));
            })
            ->status(request('status'))//in_stock , low_stock , out_of_stock
            ->with([
                'stockExtras.group.translation' => fn($q) => $q->actualTranslation($this->lang),
            ])
            ->orderBy(request('column', 'id'), request('sort', 'desc'))
            ->withTrashed()
            ->when($perPage,
                fn($q) => $q->paginate($perPage),
                fn($q) => $q->get());
    }

    public function variationsReportChartCache()
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();
        return $this->variationsReportChart($dateFrom, $dateTo);

//        return Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), $ttl,
//            function () use ($dateFrom, $dateTo) {
//                return $this->variationsReportChart($dateFrom, $dateTo);
//            });
    }

    public function variationsReportChart($dateFrom, $dateTo)
    {
        $itemsSold = moneyFormatter($this->itemsSoldQuery($dateFrom, $dateTo)
            ->selectRaw('IFNULL(TRUNCATE(sum(quantity),1), 0) as quantities_sum')
            ->value('quantities_sum'));
        $netSales = moneyFormatter($this->netSalesQuery($dateFrom, $dateTo)
            ->netSalesSum()
            ->value('net_sales_sum'));
        $ordersCount = moneyFormatter($this->ordersCountQuery($dateFrom, $dateTo)
            ->selectRaw('IFNULL(COUNT(id), 0) as orders_count')
            ->value('orders_count'));

        switch (request('chart', 'items_sold')) {
            case 'orders_count':
                $chart = $this->ordersCount($dateFrom, $dateTo);
                break;
            case 'net_sales':
                $chart = $this->netSales($dateFrom, $dateTo);
                break;
            default:
                $chart = $this->itemsSold($dateFrom, $dateTo);
                break;
        }

        $defaultCurrency = defaultCurrency();

        return compact('netSales', 'ordersCount', 'chart', 'defaultCurrency', 'itemsSold');
    }

    public function itemsSold($dateFrom, $dateTo, $id = null)
    {
        return $this->itemsSoldQuery($dateFrom, $dateTo, [$id])
            ->select(
                DB::raw("TRUNCATE(sum(quantity),2) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function itemsSoldQuery($dateFrom, $dateTo, $stockIds = null)
    {
        return OrderProduct::when($stockIds && $stockIds[0] !== null,
            fn($q) => $q->whereIn('stock_id', $stockIds))
            ->when(request('shops'), function (Builder $query) {
                $query->whereHas('stock.countable',
                    fn($q) => $q->whereIn('shop_id', request('shops')));
            })
            ->when(request('sellers'), function (Builder $query) {
                $query->whereHas('stock.countable.shop',
                    fn($q) => $q->whereIn('user_id', request('sellers')));
            })
            ->whereHas('detail', function ($detail) use ($dateFrom, $dateTo) {
                $detail->whereHas('order',
                    fn($q) => $q->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                        ->where('status', Order::DELIVERED));
            });
    }

    public function netSales($dateFrom, $dateTo, $id = null)
    {
        return $this->netSalesQuery($dateFrom, $dateTo, [$id])
            ->select(
                DB::raw(OrderDetail::NETSALESSUMQUERY . " as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function netSalesQuery($dateFrom, $dateTo, $stockIds = null)
    {
        return OrderDetail::whereStatus(OrderDetail::DELIVERED)
            ->whereHas('products', function ($orderProducts) use ($stockIds) {
                $orderProducts
                    ->when($stockIds && $stockIds[0] !== null,
                        fn($q) => $q->whereIn('stock_id', $stockIds))
                    ->when(request('shops'), function (Builder $query) {
                        $query->whereHas('stock.countable',
                            fn($q) => $q->whereIn('shop_id', request('shops')));
                    })
                    ->when(request('sellers'), function (Builder $query) {
                        $query->whereHas('stock.countable.shop',
                            fn($q) => $q->whereIn('user_id', request('sellers')));
                    })
                    ->groupBy('stock_id');
            })
            ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]));
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

    public function ordersCountQuery($dateFrom, $dateTo, $stockIds = null)
    {
        return Order::whereHas('orderDetails', function ($detail) use ($stockIds) {
            $detail
                //->whereStatus(OrderDetail::DELIVERED)
                ->whereHas('products', function ($orderProducts) use ($stockIds) {
                    $orderProducts
                        ->when($stockIds && $stockIds[0] !== null,
                            fn($q) => $q->whereIn('stock_id', $stockIds))
                        ->when(request('shops'), function (Builder $query) {
                            $query->whereHas('stock.countable',
                                fn($q) => $q->whereIn('shop_id', request('shops')));
                        })
                        ->when(request('sellers'), function (Builder $query) {
                            $query->whereHas('stock.countable.shop',
                                fn($q) => $q->whereIn('user_id', request('sellers')));
                        })
                        ->groupBy('stock_id');
                });
        })
            ->where('orders.status', Order::DELIVERED)
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo]);
    }

    public function stockReportPaginate($perPage)
    {
        $query = Stock::query()
            ->select(['stocks.id',
                'stocks.quantity',
                'products.id as product_id',
                'products.shop_id as shop_id',
                'products.bar_code as product_bar_code',
                'pt.title as product_translation_title',
                'sht.title as shop_translation_title',
                'seller.firstname as seller_firstname',
                'seller.lastname as seller_lastname',
                'seller.id as seller_id',
                'status' => DB::raw("CASE WHEN stocks.quantity<=0 THEN 'Out of stock' WHEN (stocks.quantity>0 and stocks.quantity<=5) THEN 'Low stock' ELSE 'In stock' END AS 'status'"),
                'stocks.deleted_at'])
            ->where('countable_type', Product::class)
            ->when(request('products'), function ($query) {
                $query->whereIn('products.id', request('products'));
            })
            ->when(request('categories'), function ($query) {
                $query->whereHas('countable', fn($product) => $product->whereIn('category_id', request('categories')));
            })
            ->join('products', function (JoinClause $join) {
                $join->on('products.id', '=', 'stocks.countable_id')
                    ->join('product_translations as pt', function (JoinClause $join) {
                        $join->on('products.id', '=', 'pt.product_id')
                            ->where('pt.locale', app()->getLocale());
                    });
            })
            ->join('shops as sh', function (JoinClause $join) {
                $join->on('sh.id', '=', 'products.shop_id')
                    ->leftJoin('shop_translations as sht', function ($join) {
                        $join->on('sh.id', '=', 'sht.shop_id')
                            ->where('sht.locale', app()->getLocale());
                    })
                    ->join('users as seller', 'sh.user_id', '=', 'seller.id');
            })
            ->when(request('shops'), function ($query) {
                $query->whereIn('products.shop_id', request('shops'));
            })
            ->when(request('sellers'), function (Builder $query) {
                $query->whereIn('sh.user_id', request('sellers'));
            })
            ->when(request('status'), function (Builder $query) {//in_stock , low_stock , out_of_stock
                if (request('status') === 'out_of_stock') {
                    $query->where('stocks.quantity', '<=', 0);
                } elseif (request('status') === 'low_stock') {
                    $query->where('stocks.quantity', '>', 0)
                        ->where('stocks.quantity', '<=', 5);
                } elseif (request('status') === 'in_stock') {
                    $query->where('stocks.quantity', '>', 5);
                }
            })
            ->with([
                //'countable:id,uuid,active,category_id,bar_code',
                //'countable.translation:id,product_id,locale,title',
                'stockExtras.group.translation' => fn($q) => $q->actualTranslation($this->lang),
            ])
            ->orderBy(request('column', 'id'), request('sort', 'desc'));

        if (request('export') === 'excel') {
            $name = 'products-report-' . Str::random(8);

//            Excel::store(new StockReportExport($query->get()), "export/$name.xlsx", 'public');
            ExportJob::dispatchAfterResponse("export/$name.xlsx", $query->get(), StockReportExport::class);

            return [
                'path' => 'public/export',
                'file_name' => "export/$name.xlsx",
                'link' => URL::to("storage/export/$name.xlsx"),
            ];
        }

        return $query->paginate($perPage);
    }

    public function variationsReportCompareCache()
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();

        return Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), $ttl,
            function () use ($dateFrom, $dateTo) {
                return $this->variationsReportCompare($dateFrom, $dateTo);
            });
    }

    public function getStockById($id)
    {
        $model = $this->model()
            ->select(['stocks.id as id', 'pt.title as translation_title'])
            ->where('stocks.id', $id)
            ->join('products', function (JoinClause $join) {
                $join->on('products.id', '=', 'stocks.countable_id')
                    ->join('product_translations as pt', function (JoinClause $join) {
                        $join->on('products.id', '=', 'pt.product_id')
                            ->where('pt.locale', app()->getLocale());
                    });
            })
            ->with([
                'stockExtras.group.translation' => fn($q) => $q->actualTranslation($this->lang),
            ])
            ->withTrashed()
            ->first();

        $model->translation_title = $model->translation_title . "($model->extrasFormatted)";

        return CompareResource::make($model);
    }

    public function variationsReportCompare($dateFrom, $dateTo): array
    {
        $itemsSold = moneyFormatter($this->itemsSoldQuery($dateFrom, $dateTo, request('ids'))
            ->selectRaw('IFNULL(TRUNCATE(sum(quantity),2), 0) as quantities_sum')
            ->value('quantities_sum'));
        $netSales = moneyFormatter($this->netSalesQuery($dateFrom, $dateTo, request('ids'))
            ->netSalesSum()
            ->value('net_sales_sum'));
        $ordersCount = moneyFormatter($this->ordersCountQuery($dateFrom, $dateTo, request('ids'))
            ->selectRaw('IFNULL(COUNT(id), 0) as orders_count')
            ->value('orders_count'));

        $defaultCurrency = defaultCurrency();

        $charts = [];

        $getChartData = function ($dateFrom, $dateTo, $id) {
            switch (request('chart', 'items_sold')) {
                case 'orders_count':
                    return $this->ordersCount($dateFrom, $dateTo, $id);
                case 'net_sales':
                    return $this->netSales($dateFrom, $dateTo, $id);
                default:
                    return $this->itemsSold($dateFrom, $dateTo, $id);
            }
        };

        foreach (request('ids') as $id) {
            $charts[] = [
                'translation' => $this->getStockById($id),
                'chart' => $getChartData($dateFrom, $dateTo, $id),
            ];
        }

        return compact('itemsSold', 'netSales', 'ordersCount', 'charts', 'defaultCurrency');
    }
}
