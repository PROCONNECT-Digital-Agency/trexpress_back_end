<?php

namespace App\Models;

use App\Traits\SetCurrency;
use Database\Factories\StockFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * App\Models\Stock
 *
 * @property int $id
 * @property string $countable_type
 * @property int $countable_id
 * @property float $price
 * @property int $quantity
 * @property Carbon|null $deleted_at
 * @property string|null $url
 * @property-read Model|Eloquent $countable
 * @property-read Discount|null $discount
 * @property-read Collection<int, StockExtra> $extras
 * @property-read int|null $extras_count
 * @property-read mixed $actual_discount
 * @property-read mixed $discount_expired
 * @property-read mixed $tax_price
 * @property-read Collection<int, OrderProduct> $orderProducts
 * @property-read int|null $order_products_count
 * @property-write mixed $currency_id
 * @property-read Collection<int, ExtraValue> $stockExtras
 * @property-read int|null $stock_extras_count
 * @method static StockFactory factory(...$parameters)
 * @method static Builder|Stock newModelQuery()
 * @method static Builder|Stock newQuery()
 * @method static Builder|Stock onlyTrashed()
 * @method static Builder|Stock query()
 * @method static Builder|Stock status($status = null)
 * @method static Builder|Stock whereCountableId($value)
 * @method static Builder|Stock whereCountableType($value)
 * @method static Builder|Stock whereDeletedAt($value)
 * @method static Builder|Stock whereId($value)
 * @method static Builder|Stock wherePrice($value)
 * @method static Builder|Stock whereQuantity($value)
 * @method static Builder|Stock whereUrl($value)
 * @method static Builder|Stock withTrashed()
 * @method static Builder|Stock withoutTrashed()
 * @mixin Eloquent
 */
class Stock extends Model
{
    use HasFactory, SoftDeletes,SetCurrency;
    protected $fillable = ['price', 'quantity', 'extras','countable_id','countable_type','url'];
    public $timestamps = false;

    protected $casts = [
        'extras' => 'array'
    ];

    protected $hidden = [
        'pivot'
    ];

    public function countable(): MorphTo
    {
        return $this->morphTo('countable')->withTrashed();
    }

    public function discount(){
        return $this->hasOneThrough(Discount::class, ProductDiscount::class,
            'product_id', 'id', 'countable_id', 'discount_id')
            ->whereDate('start', '<=', today())->whereDate('end', '>=', today())
            ->where('active', 1)->orderByDesc('id');
    }

    public function stockExtras()
    {
        return $this->belongsToMany(ExtraValue::class, StockExtra::class)->orderBy('extra_group_id');
    }

    public function extras()
    {
        return $this->hasMany(StockExtra::class);
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function getPriceAttribute($value)
    {
        return $value * $this->currency();
    }

    public function setCurrencyIdAttribute()
    {
        $this->attributes['currency_id'] = request('currency_id');
    }

    public function getActualDiscountAttribute($value)
    {

        if (isset($this->discount->type)) {
            if ($this->discount->type == 'percent') {
                $price = $this->discount->price / 100 * $this->price;
            } else {
                $price = $this->discount->price * $this->currency();
            }
            return $price;
        }
        return 0;
    }

    public function getDiscountExpiredAttribute($value)
    {

        return $this->discount->end ?? null;
    }

    public function getTaxPriceAttribute($value)
    {
        $tax = $this->countable->tax ?? 0;
        return (($this->price - $this->actualDiscount) / 100) * $tax;
    }

    public function scopeStatus($query, $status = null)
    {
        $query->where(function ($query) use ($status) {
            if ($status === 'out_of_stock') {
                $query->where('stocks.quantity', '<=', 0);
                $query->where('stocks.quantity', '<=', 0);
            } elseif ($status === 'low_stock') {
                $query->where('stocks.quantity', '>', 0)
                    ->where('stocks.quantity', '<=', 5);
                $query->where('stocks.quantity', '>', 0)
                    ->where('stocks.quantity', '<=', 5);
                //$query->where(['stocks.quantity', '>', 0, 'stocks.quantity' <= 5]);
            } elseif ($status === 'in_stock') {
                $query->where('stocks.quantity', '>', 5);
                $query->where('stocks.quantity', '>', 5);
            }
        });
    }
}
