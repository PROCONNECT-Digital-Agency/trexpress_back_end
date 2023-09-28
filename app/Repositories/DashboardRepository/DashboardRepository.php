<?php

namespace App\Repositories\DashboardRepository;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardRepository extends \App\Repositories\CoreRepository
{

    protected function getModelClass()
    {
        return Order::class;
    }

    public function statisticCount($array = [])
    {
        $orders = $this->model()
            ->with('orderDetails')
            ->when(isset($array['shop_id']),function ($q) use ($array){
                $q->whereHas('orderDetails',function ($q) use ($array){
                    $q->where('shop_id', $array['shop_id']);
                });
            })
            ->count();

        // GET ORDERS WITH DELIVERED STATUS
        $delivered = $this->model()
            ->where('status', Order::DELIVERED)
            ->when(isset($array['shop_id']),function ($q) use ($array){
                $q->whereHas('orderDetails',function ($q) use ($array){
                    $q->where('shop_id', $array['shop_id']);
                });
            })->count();

        // GET ORDERS WITH CANCELED STATUS
        $canceled = $this->model()
            ->where('status', Order::CANCELED)
            ->when(isset($array['shop_id']),function ($q) use ($array){
                $q->whereHas('orderDetails',function ($q) use ($array){
                    $q->where('shop_id', $array['shop_id']);
                });
            })
            ->count();

        // GET ORDERS WITH PROGRESS STATUS
        $progress = $this->model()
            ->whereIn('status', [
                Order::NEW,
                Order::READY,
                Order::ON_A_WAY,
            ])
            ->when(isset($array['shop_id']),function ($q) use ($array){
                $q->whereHas('orderDetails',function ($q) use ($array){
                    $q->where('shop_id', $array['shop_id']);
                });
            })
            ->count();

        // GET PRODUCTS OUT OF STOCK COUNT
        $productsOutOfStock = Product::where('active', 1)
            ->when(isset($array['shop_id']), function ($shop) use ($array) {
                $shop->where('shop_id', $array['shop_id']);
            })
            ->whereHas('shop')
            ->whereHas('stocks', function ($item) {
                $item->where('price', '>', 0)->where('quantity', '=', 0);
            })->count();


        // GET PRODUCTS COUNT
        $products = Product::where('active', 1)
            ->when(isset($array['shop_id']), function ($shop) use ($array) {
                $shop->where('shop_id', $array['shop_id']);
            })
            ->whereHas('shop', function ($item) {
                $item->where('status', 'approved');
            })
            ->whereHas('stocks')->count();

        // GET REVIEWS COUNT
        $reviews = DB::table('reviews as r')
            ->leftJoin('order_details as o_d','o_d.id','=','r.reviewable_id')
            ->when(isset($array['shop_id']),function ($q) use ($array) {
                $q->where('shop_id',$array['shop_id']);
            })->where('reviewable_type', OrderDetail::class)
            ->count();
//            ->when(isset($array['shop_id']),function ($q) use ($array){
//                $q->whereHas('reviewable',function ($q) use ($array){
//                    $q->where('shop_id',$array['shop_id']);
//                });
//            })

        return [
            'products_out_of_count' => $productsOutOfStock,
            'products_count' => $products,
            'progress_orders_count' => $progress,
            'delivered_orders_count' => $delivered,
            'cancel_orders_count' => $canceled,
            'orders_count' => $orders,
            'reviews_count' => $reviews,
        ];
    }


    public function sellerStatisticCount($array = [])
    {
        $orders = $this->model()
            ->with('orderDetails')
            ->when(isset($array['shop_id']),function ($q) use ($array){
                $q->whereHas('orderDetails',function ($q) use ($array){
                    $q->where('shop_id', $array['shop_id']);
                });
            })
            ->count();

        // GET ORDERS WITH DELIVERED STATUS
        $delivered = OrderDetail::where('shop_id', $array['shop_id'])
            ->where('status', Order::DELIVERED)
            ->count();

        // GET ORDERS WITH CANCELED STATUS
        $canceled = OrderDetail::where('shop_id', $array['shop_id'])
            ->where('status', Order::CANCELED)
            ->count();

        // GET ORDERS WITH PROGRESS STATUS
        $progress = OrderDetail::where('status','!=',[OrderDetail::CANCELED])
            ->where('status','!=',[OrderDetail::DELIVERED])
            ->where('shop_id', $array['shop_id'])
            ->count();

        // GET PRODUCTS OUT OF STOCK COUNT
        $productsOutOfStock = Product::where('active', 1)
            ->when(isset($array['shop_id']), function ($shop) use ($array) {
                $shop->where('shop_id', $array['shop_id']);
            })
            ->whereHas('shop')
            ->whereHas('stocks', function ($item) {
                $item->where('price', '>', 0)->where('quantity', '=', 0);
            })->count();


        // GET PRODUCTS COUNT
        $products = Product::where('active', 1)
            ->when(isset($array['shop_id']), function ($shop) use ($array) {
                $shop->where('shop_id', $array['shop_id']);
            })
            ->whereHas('shop', function ($item) {
                $item->where('status', 'approved');
            })
            ->whereHas('stocks')->count();

        // GET REVIEWS COUNT
        $reviews = DB::table('reviews as r')
            ->leftJoin('order_details as o_d','o_d.id','=','r.reviewable_id')
            ->when(isset($array['shop_id']),function ($q) use ($array) {
                $q->where('shop_id',$array['shop_id']);
            })->where('reviewable_type', OrderDetail::class)
            ->count();
//            ->when(isset($array['shop_id']),function ($q) use ($array){
//                $q->whereHas('reviewable',function ($q) use ($array){
//                    $q->where('shop_id',$array['shop_id']);
//                });
//            })

        return [
            'products_out_of_count' => $productsOutOfStock,
            'products_count' => $products,
            'progress_orders_count' => $progress,
            'delivered_orders_count' => $delivered,
            'cancel_orders_count' => $canceled,
            'orders_count' => $orders,
            'reviews_count' => $reviews,
        ];
    }

    public function statisticSum($array = [])
    {
        $time = $array['time'] ?? 'subMonth';

        return OrderDetail::where('status', 'delivered')
            ->when(isset($array['shop_id']), function ($shop) use ($array) {
                $shop->where('shop_id', $array['shop_id']);
            })
            ->whereDate('created_at', '>', now()->{$time}())
            ->get(
            array(
                DB::raw('SUM(price) as total_earned'),
                DB::raw('SUM(delivery_fee) as delivery_earned'),
                DB::raw('SUM(tax) as tax_earned'),
                DB::raw('SUM(commission_fee) as commission_earned')
            )
        );
    }

    public function statisticTopCustomer($perPage, $array = [])
    {
        $time = $array['time'] ?? 'subMonth';

        return User::whereDate('created_at', '>', now()->{$time}())
            ->when(isset($array['shop_id']), function ($shop) use ($array) {
                $shop->whereHas('orderDetails', function ($q) use ($array) {
                    $q->where('shop_id', $array['shop_id']);
                })
                ->withSum(['orderDetails' =>  function ($q) use ($array) {
                    $q->where('shop_id', $array['shop_id']);
                }], 'price');
            }, function ($q) {
                $q->withSum('orderDetails', 'price');
            })
            ->orderByDesc('order_details_sum_price')
            ->paginate($perPage);
    }

    public function statisticTopSoldProducts($perPage, $array = [])
    {
        $time = $array['time'] ?? 'subMonth';

        return Product::with([
            'category',
            'translation' => function($q) {
                $q->actualTranslation($this->setLanguage());
            }
        ])->when(isset($array['shop_id']), function ($shop) use ($array) {
            $shop->where('shop_id', $array['shop_id']);
        })
            ->withCount('orders')
            ->where('active', 1)
            ->whereDate('created_at', '>', now()->{$time}())
            ->orderByDesc('orders_count')
            ->paginate($perPage);
    }

    public function statisticOrdersSales($array = [])
    {
        $time = $array['time'] ?? 'subYear';

        return $this->model()->whereHas('orderDetails', function ($item) use ($array) {
            $item->when(isset($array['shop_id']), function ($shop) use ($array) {
                $shop->where('shop_id', $array['shop_id']);
            })->where('status', 'delivered');
        })
            ->whereDate('created_at', '>', now()->{$time}())
            ->selectRaw('DATE(created_at) as date, ROUND(SUM(price), 2) as price')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
    }

    public function statisticOrdersCount($array = [])
    {
        $time = $array['time'] ?? 'subYear';

        return $this->model()->whereHas('orderDetails', function ($item) use ($array) {
            $item->when(isset($array['shop_id']), function ($shop) use ($array) {
                $shop->where('shop_id', $array['shop_id']);
            });
        })
            ->whereDate('created_at', '>', now()->{$time}())
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
    }
}
