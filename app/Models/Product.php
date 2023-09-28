<?php

namespace App\Models;

use App\Traits\Countable;
use App\Traits\Loadable;
use App\Traits\Reviewable;
use App\Traits\SetCurrency;
use Database\Factories\ProductFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property string $uuid
 * @property int $shop_id
 * @property int $category_id
 * @property int|null $unit_id
 * @property string|null $keywords
 * @property float|null $tax
 * @property string $status
 * @property int|null $min_qty
 * @property int|null $max_qty
 * @property int $active
 * @property string|null $img
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $bar_code
 * @property int|null $brand_id
 * @property-read Brand|null $brand
 * @property-read Category $category
 * @property-read Collection<int, Discount> $discount
 * @property-read int|null $discount_count
 * @property-read Collection<int, ExtraGroup> $extras
 * @property-read int|null $extras_count
 * @property-read Collection<int, Gallery> $galleries
 * @property-read int|null $galleries_count
 * @property-read Collection<int, OrderProduct> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, OrderProduct> $productSales
 * @property-read int|null $product_sales_count
 * @property-read Collection<int, ProductProperties> $properties
 * @property-read int|null $properties_count
 * @property-read Review|null $review
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read Shop $shop
 * @property-read Model|Eloquent $stock
 * @property-read Collection<int, Stock> $stocks
 * @property-read int|null $stocks_count
 * @property-read Collection<int, Stock> $stocksWithTrashed
 * @property-read int|null $stocks_with_trashed_count
 * @property-read ProductTranslation|null $translation
 * @property-read Collection<int, ProductTranslation> $translations
 * @property-read int|null $translations_count
 * @property-read Unit|null $unit
 * @method static ProductFactory factory(...$parameters)
 * @method static Builder|Product filter($array)
 * @method static Builder|Product newModelQuery()
 * @method static Builder|Product newQuery()
 * @method static Builder|Product onlyTrashed()
 * @method static Builder|Product query()
 * @method static Builder|Product updatedDate($updatedDate)
 * @method static Builder|Product whereActive($value)
 * @method static Builder|Product whereBarCode($value)
 * @method static Builder|Product whereBrandId($value)
 * @method static Builder|Product whereCategoryId($value)
 * @method static Builder|Product whereCreatedAt($value)
 * @method static Builder|Product whereDeletedAt($value)
 * @method static Builder|Product whereId($value)
 * @method static Builder|Product whereImg($value)
 * @method static Builder|Product whereKeywords($value)
 * @method static Builder|Product whereMaxQty($value)
 * @method static Builder|Product whereMinQty($value)
 * @method static Builder|Product whereShopId($value)
 * @method static Builder|Product whereTax($value)
 * @method static Builder|Product whereUnitId($value)
 * @method static Builder|Product whereUpdatedAt($value)
 * @method static Builder|Product whereUuid($value)
 * @method static Builder|Product withTrashed()
 * @method static Builder|Product withoutTrashed()
 * @mixin Eloquent
 */
class Product extends Model
{
    use HasFactory, SoftDeletes, Countable, Loadable, Reviewable, SetCurrency;

    const PUBLISHED     = 'published';
    const PENDING       = 'pending';
    const UNPUBLISHED   = 'unpublished';

    const STATUSES = [
        self::PUBLISHED     => self::PUBLISHED,
        self::PENDING       => self::PENDING,
        self::UNPUBLISHED   => self::UNPUBLISHED,
    ];

    protected $guarded = [];

    // Translations
    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(ProductTranslation::class);
    }

    // Product Shop
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    // Product Category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Product Orders
    public function productSales(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'product_id');
    }

    // Product Brand
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    // Product Properties
    public function properties(): HasMany
    {
        return $this->hasMany(ProductProperties::class);
    }

    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(OrderProduct::class, Stock::class,
            'countable_id', 'stock_id', 'id', 'id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function extras(): BelongsToMany
    {
        return $this->belongsToMany(ExtraGroup::class, ProductExtra::class);
    }

    public function discount(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, ProductDiscount::class);
    }

    public function scopeUpdatedDate($query, $updatedDate)
    {
        return $query->where('updated_at', '>', $updatedDate);
    }

    public function scopeFilter($query, $array)
    {
        $query
            ->when(isset($array['range'][0]) || isset($array['range'][1]), function ($q) use ($array) {
                $q->whereHas('stocks', function ($stock) use ($array) {
                    $stock->whereBetween('price', [$array['range'][0] ?? 0.1, $array['range'][1] ?? 10000000000]);
                });
            })
            ->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->where('shop_id', $array['shop_id']);
            })
            ->when(isset($array['rest']), function ($q) {
                $q->whereNotNull('img');
            })
            ->when(isset($array['category_id']) && is_array($array['category_id']), function ($q) use ($array) {
                $q->whereIn('category_id', $array['category_id']);
            })
            ->when(isset($array['category_id']) && is_string($array['category_id']), function ($q) use ($array) {
                $q->where('category_id', $array['category_id']);
            })
            ->when(isset($array['brand_id']), function ($q) use ($array) {
                $q->where('brand_id', $array['brand_id']);
            })
            ->when(isset($array['column_rate']), function ($q) use ($array) {
                $q->whereHas('reviews', function ($review) use ($array) {
                    $review->orderBy('rating', $array['sort']);
                });
            })
            ->when(isset($array['column_order']), function ($q) use ($array) {
                $q->withCount('orders')->orderBy('orders_count', $array['sort']);
            })
            ->when(isset($array['column_price']), function ($q) use ($array) {
                $q->withAvg('stocks', 'price')->orderBy('stocks_avg_price', $array['sort']);
            })
            ->when(isset($array['status']),function ($q) use ($array){
                $q->where('status',$array['status']);
            });
    }
}
