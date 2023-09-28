<?php

namespace App\Models;

use App\Traits\Loadable;
use App\Traits\SetCurrency;
use Carbon\Carbon;
use Database\Factories\ShopFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Shop
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property float $tax
 * @property int|null $delivery_range
 * @property float $percentage
 * @property array|null $location
 * @property string|null $phone
 * @property int|null $show_type
 * @property int $open
 * @property int $visibility
 * @property \Illuminate\Support\Carbon $open_time
 * @property \Illuminate\Support\Carbon $close_time
 * @property string|null $background_img
 * @property string|null $logo_img
 * @property float $min_amount
 * @property string $status
 * @property string|null $status_note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $mark
 * @property-read Collection<int, Delivery> $deliveries
 * @property-read int|null $deliveries_count
 * @property-read Collection<int, Gallery> $galleries
 * @property-read int|null $galleries_count
 * @property-read mixed $working_status
 * @property-read Collection<int, Invitation> $invitations
 * @property-read int|null $invitations_count
 * @property-read Collection<int, OrderDetail> $orderDetails
 * @property-read int|null $order_details_count
 * @property-read Collection<int, OrderDetail> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, PointDelivery> $pointDeliveries
 * @property-read int|null $point_deliveries_count
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read User $seller
 * @property-read ShopSubscription|null $subscription
 * @property-read ShopTranslation|null $translation
 * @property-read Collection<int, ShopTranslation> $translations
 * @property-read int|null $translations_count
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 * @method static ShopFactory factory(...$parameters)
 * @method static Builder|Shop filter($array)
 * @method static Builder|Shop newModelQuery()
 * @method static Builder|Shop newQuery()
 * @method static Builder|Shop onlyTrashed()
 * @method static Builder|Shop query()
 * @method static Builder|Shop updatedDate($updatedDate)
 * @method static Builder|Shop whereBackgroundImg($value)
 * @method static Builder|Shop whereCloseTime($value)
 * @method static Builder|Shop whereCreatedAt($value)
 * @method static Builder|Shop whereDeletedAt($value)
 * @method static Builder|Shop whereDeliveryRange($value)
 * @method static Builder|Shop whereId($value)
 * @method static Builder|Shop whereLocation($value)
 * @method static Builder|Shop whereLogoImg($value)
 * @method static Builder|Shop whereMark($value)
 * @method static Builder|Shop whereMinAmount($value)
 * @method static Builder|Shop whereOpen($value)
 * @method static Builder|Shop whereOpenTime($value)
 * @method static Builder|Shop wherePercentage($value)
 * @method static Builder|Shop wherePhone($value)
 * @method static Builder|Shop whereShowType($value)
 * @method static Builder|Shop whereStatus($value)
 * @method static Builder|Shop whereStatusNote($value)
 * @method static Builder|Shop whereTax($value)
 * @method static Builder|Shop whereUpdatedAt($value)
 * @method static Builder|Shop whereUserId($value)
 * @method static Builder|Shop whereUuid($value)
 * @method static Builder|Shop whereVisibility($value)
 * @method static Builder|Shop withTrashed()
 * @method static Builder|Shop withoutTrashed()
 * @mixin Eloquent
 */
class Shop extends Model
{
    use HasFactory, SoftDeletes, Loadable, SetCurrency;
    protected $guarded = [];

    const NEW = 'new';
    const EDITED = 'edited';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const INACTIVE = 'inactive';

    const STATUS = [
        'new',
        'edited',
        'approved',
        'rejected',
        'inactive'
    ];

    protected $casts = [
        'location' => 'array',
        'open_time' => 'datetime',
        'close_time' => 'datetime',
    ];

    public function getWorkingStatusAttribute($value)
    {
        return $this->open && (now()->greaterThan(Carbon::parse($this->open_time)) && now()->lessThan(Carbon::parse($this->close_time)));
    }

    public function scopeUpdatedDate($query, $updatedDate)
    {
        return $query->where('updated_at', '>', $updatedDate);
    }

    public function translations() {
        return $this->hasMany(ShopTranslation::class);
    }

    public function translation() {
        return $this->hasOne(ShopTranslation::class);
    }

    public function deliveries() {
        return $this->hasMany(Delivery::class, 'shop_id');
    }

    public function seller() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users() {
        return $this->hasManyThrough(User::class, Invitation::class,
            'shop_id', 'id', 'id', 'user_id');
    }

    public function products() {
        return $this->hasMany(Product::class);
    }

    public function orderDetails() {
        return $this->hasMany(OrderDetail::class);
    }

    public function invitations() {
        return $this->hasMany(Invitation::class);
    }

    public function orders() {
        return $this->hasMany(OrderDetail::class);
    }

    public function reviews() {
        return $this->hasManyThrough(Review::class, OrderDetail::class,
        'shop_id', 'reviewable_id');
    }

    public function subscription()
    {
        return $this->hasOne(ShopSubscription::class, 'shop_id')
            ->whereDate('expired_at', '>=', today())->where(['active' => 1])->orderByDesc('id');
    }

    public function pointDeliveries(): HasMany
    {
        return $this->hasMany(PointDelivery::class);
    }

    public function scopeFilter($value, $array)
    {
        return $value
            ->when(isset($array['user_id']), function ($q) use ($array) {
                $q->where('user_id', $array['user_id']);
            })
            ->when(isset($array['status']), function ($q) use ($array) {
                $q->where('status', $array['status']);
            })
            ->when(isset($array['visibility']), function ($q) use ($array) {
                $q->where('visibility', $array['visibility']);
            })
            ->when(isset($array['open']), function ($q) use ($array) {
                $q->where('open_time', '<=', now()->format('H:i'))
                    ->where('close_time', '>=', now()->format('H:i'));
            })->when(isset($array['always_open']), function ($q) use ($array) {
                $q->whereColumn('open_time', '=', 'close_time');
            })
            ->when(isset($array['delivery']), function ($q) use ($array) {
                $q->whereHas('deliveries', function ($q) use($array) {
                    $q->where('type', $array['delivery']);
                });
            });
    }
}
