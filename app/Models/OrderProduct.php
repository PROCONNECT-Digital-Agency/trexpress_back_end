<?php

namespace App\Models;

use Database\Factories\OrderProductFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\OrderProduct
 *
 * @property int $id
 * @property int $order_detail_id
 * @property int $stock_id
 * @property float $origin_price
 * @property float $total_price
 * @property float $tax
 * @property float $discount
 * @property int $quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read OrderDetail $detail
 * @property-read Stock $stock
 * @property-read ProductTranslation|null $translation
 * @method static OrderProductFactory factory(...$parameters)
 * @method static Builder|OrderProduct netSales()
 * @method static Builder|OrderProduct netSalesSum()
 * @method static Builder|OrderProduct newModelQuery()
 * @method static Builder|OrderProduct newQuery()
 * @method static Builder|OrderProduct query()
 * @method static Builder|OrderProduct whereCreatedAt($value)
 * @method static Builder|OrderProduct whereDiscount($value)
 * @method static Builder|OrderProduct whereId($value)
 * @method static Builder|OrderProduct whereOrderDetailId($value)
 * @method static Builder|OrderProduct whereOriginPrice($value)
 * @method static Builder|OrderProduct whereQuantity($value)
 * @method static Builder|OrderProduct whereStockId($value)
 * @method static Builder|OrderProduct whereTax($value)
 * @method static Builder|OrderProduct whereTotalPrice($value)
 * @method static Builder|OrderProduct whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OrderProduct extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function translation(): HasOne
    {
        return $this->hasOne(ProductTranslation::class, 'product_id', 'product_id');
    }

    public function detail()
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id');
    }

    public function stock() {
        return $this->belongsTo(Stock::class)->withTrashed();
    }

    const NETSALESSUMQUERY = 'IFNULL(TRUNCATE( CAST( SUM(total_price) as decimal(7,2)) ,2) ,0)';

    public function scopeNetSalesSum($query)
    {
        return $query->selectRaw(self::NETSALESSUMQUERY . " as net_sales_sum");
    }

    public function scopeNetSales($query)
    {
        return $query->selectRaw(self::NETSALESSUMQUERY . " as net_sales");
    }

    public function getOriginPriceAttribute($value): float
    {
        $rate = Currency::where('id',$this->detail->order->currency_id)->first()?->rate;

        if (auth('sanctum')->check() && request()->is('api/v1/dashboard/user/*') && isset($rate)){
            return round($value * $rate, 2);
        } else {
            return $value;
        }
    }

    public function getTaxAttribute($value): float
    {
        $rate = Currency::where('id',$this->detail->order->currency_id)->first()?->rate;

        if (auth('sanctum')->check() && request()->is('api/v1/dashboard/user/*') && isset($rate)){
            return round($value * $rate, 2);
        } else {
            return $value;
        }
    }

    public function getDiscountAttribute($value): float
    {
        $rate = Currency::where('id',$this->detail->order->currency_id)->first()?->rate;

        if (auth('sanctum')->check() && request()->is('api/v1/dashboard/user/*') && isset($rate)){
            return round($value * $rate, 2);
        } else {
            return $value;
        }
    }

    public function getTotalPriceAttribute($value): float
    {
        $rate = Currency::where('id',$this->detail->order->currency_id)->first()?->rate;

        if (auth('sanctum')->check() && request()->is('api/v1/dashboard/user/*') && isset($rate)){
            return round($value * $rate, 2);
        } else {
            return $value;
        }
    }
}
