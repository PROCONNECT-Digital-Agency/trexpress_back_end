<?php

namespace App\Models;

use App\Traits\Notification;
use App\Traits\Payable;
use App\Traits\Reviewable;
use Database\Factories\OrderDetailFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\OrderDetail
 *
 * @property int $id
 * @property int $order_id
 * @property int $shop_id
 * @property float $price
 * @property float $tax
 * @property float|null $commission_fee
 * @property string $status
 * @property int|null $delivery_address_id
 * @property int|null $delivery_type_id
 * @property float $delivery_fee
 * @property int|null $deliveryman
 * @property string|null $delivery_date
 * @property string|null $delivery_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read UserAddress|null $deliveryAddress
 * @property-read User|null $deliveryMan
 * @property-read Delivery|null $deliveryType
 * @property-read Order $order
 * @property-read OrderProduct|null $orderStock
 * @property-read Collection<int, OrderProduct> $orderStocks
 * @property-read int|null $order_stocks_count
 * @property-read Collection<int, OrderProduct> $products
 * @property-read int|null $products_count
 * @property-read Review|null $review
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read Shop $shop
 * @property-read Collection<int, Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static OrderDetailFactory factory(...$parameters)
 * @method static Builder|OrderDetail filter($array)
 * @method static Builder|OrderDetail netSales()
 * @method static Builder|OrderDetail netSalesSum()
 * @method static Builder|OrderDetail newModelQuery()
 * @method static Builder|OrderDetail newQuery()
 * @method static Builder|OrderDetail query()
 * @method static Builder|OrderDetail updatedDate($updatedDate)
 * @method static Builder|OrderDetail whereCommissionFee($value)
 * @method static Builder|OrderDetail whereCreatedAt($value)
 * @method static Builder|OrderDetail whereDeletedAt($value)
 * @method static Builder|OrderDetail whereDeliveryAddressId($value)
 * @method static Builder|OrderDetail whereDeliveryDate($value)
 * @method static Builder|OrderDetail whereDeliveryFee($value)
 * @method static Builder|OrderDetail whereDeliveryTime($value)
 * @method static Builder|OrderDetail whereDeliveryTypeId($value)
 * @method static Builder|OrderDetail whereDeliveryman($value)
 * @method static Builder|OrderDetail whereId($value)
 * @method static Builder|OrderDetail whereOrderId($value)
 * @method static Builder|OrderDetail wherePrice($value)
 * @method static Builder|OrderDetail whereShopId($value)
 * @method static Builder|OrderDetail whereStatus($value)
 * @method static Builder|OrderDetail whereTax($value)
 * @method static Builder|OrderDetail whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OrderDetail extends Model
{
    use HasFactory, Payable, Notification, Reviewable;
    protected $guarded = [];

    const NEW = 'new';
    const READY = 'ready';
    const ON_A_WAY = 'on_a_way';
    const DELIVERED = 'delivered';
    const CANCELED = 'canceled';


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderStocks(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'order_detail_id');
    }

    public function orderStock(): HasOne
    {
        return $this->hasOne(OrderProduct::class, 'order_detail_id');
    }

    public function deliveryMan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deliveryman');
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'delivery_address_id');
    }

    public function deliveryType(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_type_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class)->withTrashed();
    }

    public function products(): HasMany
    {
        return $this->hasMany(OrderProduct::class,'order_detail_id');
    }


    public function getPriceAttribute($value)
    {
        $rate = Currency::where('id',$this->order->currency_id)->first()?->rate;
        if (request()->is('api/v1/dashboard/user/*') && isset($rate)){
            return round($value * $rate, 2);
        } else {
            return $value;
        }
    }

    public function getTaxAttribute($value)
    {
        $rate = Currency::where('id',$this->order?->currency_id)->first()?->rate;

        if (request()->is('api/v1/dashboard/user/*') && isset($this->order->rate)){
            return round($value * $this->order->rate, 2);
        } else {
            return $value;
        }
    }

    const NETSALESSUMQUERY = 'IFNULL(TRUNCATE( CAST( SUM(price - IFNULL(tax ,0)) as decimal(7,2)) ,2) ,0)';

    public function scopeNetSalesSum($query)
    {
        return $query->selectRaw(self::NETSALESSUMQUERY . " as net_sales_sum");
    }

    public function scopeNetSales($query)
    {
        return $query->selectRaw(self::NETSALESSUMQUERY . " as net_sales");
    }

    public function scopeUpdatedDate($query, $updatedDate)
    {
        return $query->where('updated_at', '>', $updatedDate);
    }

    public function scopeFilter($query, $array)
    {
        $query
            ->when(isset($array['status']), function ($q) use ($array) {
                $q->where('status', $array['status']);
            });
    }
}
