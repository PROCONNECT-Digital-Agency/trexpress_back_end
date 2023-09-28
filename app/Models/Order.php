<?php

namespace App\Models;

use App\Traits\Payable;
use App\Traits\Reviewable;
use Database\Factories\OrderFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property int $user_id
 * @property float $price
 * @property int $currency_id
 * @property int $rate
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $status
 * @property float|null $total_delivery_fee
 * @property int|null $user_address_id
 * @property float|null $tax
 * @property int $delivery_id
 * @property int|null $deliveryman_id
 * @property float|null $cash_back
 * @property-read OrderCoupon|null $coupon
 * @property-read Currency|null $currency
 * @property-read Delivery $delivery
 * @property-read User|null $deliveryMan
 * @property-read OrderDetail|null $orderDetail
 * @property-read Collection<int, OrderDetail> $orderDetails
 * @property-read int|null $order_details_count
 * @property-read PointHistory|null $point
 * @property-read Review|null $review
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read Transaction|null $transaction
 * @property-read Collection<int, Transaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read User $user
 * @property-read UserAddress|null $userAddress
 * @method static OrderFactory factory(...$parameters)
 * @method static Builder|Order filter($array)
 * @method static Builder|Order netSales()
 * @method static Builder|Order netSalesSum()
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order query()
 * @method static Builder|Order status($status)
 * @method static Builder|Order updatedDate($updatedDate)
 * @method static Builder|Order whereCashBack($value)
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereCurrencyId($value)
 * @method static Builder|Order whereDeletedAt($value)
 * @method static Builder|Order whereDeliveryId($value)
 * @method static Builder|Order whereDeliverymanId($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereNote($value)
 * @method static Builder|Order wherePrice($value)
 * @method static Builder|Order whereRate($value)
 * @method static Builder|Order whereStatus($value)
 * @method static Builder|Order whereTax($value)
 * @method static Builder|Order whereTotalDeliveryFee($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @method static Builder|Order whereUserAddressId($value)
 * @method static Builder|Order whereUserId($value)
 * @mixin Eloquent
 */
class Order extends Model
{
    use HasFactory, Payable, Reviewable;

    protected $guarded = [];

    const TYPE_PICKUP = 'pickup';

    const NEW = 'new';
    const READY = 'ready';
    const ACCEPTED = 'accepted';
    const ON_A_WAY = 'on_a_way';
    const DELIVERED = 'delivered';
    const CANCELED = 'canceled';

    const STATUS = [
        self::NEW => self::NEW,
        self::ACCEPTED => self::ACCEPTED,
        self::READY => self::READY,
        self::ON_A_WAY => self::ON_A_WAY,
        self::DELIVERED => self::DELIVERED,
        self::CANCELED => self::CANCELED,
    ];
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function orderDetail(): HasOne
    {
        return $this->hasOne(OrderDetail::class);
    }

//    public function transaction(){
//        return $this->hasOneThrough(Transaction::class, OrderDetail::class,
//        'order_id', 'payable_id', 'id', 'id');
//    }

    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'payable')->orderByDesc('id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function review()
    {
        return $this->morphOne(Review::class, 'reviewable');
    }

    public function point()
    {
        return $this->hasOne(PointHistory::class, 'order_id');
    }

    public function userAddress()
    {
        return $this->belongsTo(UserAddress::class);
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function coupon(): HasOne
    {
        return $this->hasOne(OrderCoupon::class, 'order_id');
    }

    public function deliveryMan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deliveryman_id');
    }

    public function getPriceAttribute($value)
    {

        $currency = Currency::where('id', $this->currency_id)->first();
        if (request()->is('api/v1/dashboard/user/*') && $currency) {
            return round($value * $currency->rate, 2);
        } else {
            return $value;
        }
    }

    public function getTotalDeliveryFeeAttribute($value)
    {
        $currency = Currency::where('id', $this->currency_id)->first();
        if (request()->is('api/v1/dashboard/user/*') && $currency) {
            return round($value * $currency->rate, 2);
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

    public function scopeStatus($query, $status)
    {
        $query->when($status === 'open', function ($q) {
            $q->whereIn('status', [
                Order::NEW,
                Order::READY,
                Order::ACCEPTED,
                Order::ON_A_WAY,
            ]);
        })
            ->when($status === 'completed', function ($q) {
                $q->where('status',Order::DELIVERED);
            })
            ->when($status === 'canceled', function ($q) {
                $q->where('status',Order::CANCELED);
            });
    }

    public function scopeFilter($query, $array)
    {
        $query
            ->when(data_get($array, 'deliveryman'), fn(Builder $q, $deliveryman) => $q->whereHas('deliveryMan', function ($q) use ($deliveryman) {
                $q->where('id', $deliveryman);
            })
            )
            ->when(isset($array['status']), function ($q) use ($array) {
                $q->where('status', $array['status']);
            });
    }
}
