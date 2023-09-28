<?php

namespace App\Models;

use App\Traits\Loadable;
use App\Traits\Reviewable;
use Database\Factories\UserFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $uuid
 * @property string $firstname
 * @property string|null $lastname
 * @property string|null $email
 * @property string|null $phone
 * @property Carbon|null $birthday
 * @property string $gender
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $phone_verified_at
 * @property string|null $ip_address
 * @property int $active
 * @property string|null $img
 * @property string|null $firebase_token
 * @property string|null $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $address
 * @property string|null $passport_number
 * @property string|null $passport_secret
 * @property string|null $user_delivery_id
 * @property-read Collection<int, UserAddress> $addresses
 * @property-read int|null $addresses_count
 * @property-read Collection<int, Order> $deliverymanOrders
 * @property-read int|null $deliveryman_orders_count
 * @property-read Collection<int, Gallery> $galleries
 * @property-read int|null $galleries_count
 * @property-read mixed $role
 * @property-read Collection<int, Invitation> $invitations
 * @property-read int|null $invitations_count
 * @property-read Invitation|null $invite
 * @property-read Collection<int, Banner> $likes
 * @property-read int|null $likes_count
 * @property-read Shop|null $moderatorShop
 * @property-read Collection<int, Shop> $moderatorShops
 * @property-read int|null $moderator_shops_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, OrderDetail> $orderDetails
 * @property-read int|null $order_details_count
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read UserPoint|null $point
 * @property-read Collection<int, PointHistory> $pointHistory
 * @property-read int|null $point_history_count
 * @property-read Review|null $review
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Shop|null $shop
 * @property-read Collection<int, SocialProvider> $socialProviders
 * @property-read int|null $social_providers_count
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read DeliveryManSetting|null $deliveryManSetting
 * @property-read int|null $tokens_count
 * @property-read Wallet|null $wallet
 * @property-read Wallet|null $walletHasOne
 * @method static UserFactory factory(...$parameters)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User onlyTrashed()
 * @method static Builder|User permission($permissions)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User whereActive($value)
 * @method static Builder|User whereAddress($value)
 * @method static Builder|User whereBirthday($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereFirebaseToken($value)
 * @method static Builder|User whereFirstname($value)
 * @method static Builder|User whereGender($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereImg($value)
 * @method static Builder|User whereIpAddress($value)
 * @method static Builder|User whereLastname($value)
 * @method static Builder|User wherePassportNumber($value)
 * @method static Builder|User wherePassportSecret($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User wherePhoneVerifiedAt($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUserDeliveryId($value)
 * @method static Builder|User whereUuid($value)
 * @method static Builder|User withTrashed()
 * @method static Builder|User withoutTrashed()
 * @mixin Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use Loadable;
    use SoftDeletes;
    use Reviewable;


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'birthday',
        'gender',
        'email',
        'phone',
        'img',
        'password',
        'firebase_token',
        'email_verified_at',
        'phone_verified_at',
        'deleted_at',
        'address',
        'passport_number',
        'passport_secret',
        'user_delivery_id',
        'address_email',
        'address_phone',
        'active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'birthday' => 'date',
        'firebase_token'    => 'array',

    ];

    public function isOnline()
    {
        return Cache::has('user-online-' . $this->id);
    }

    public function getRoleAttribute(){
        return $this->role = $this->roles[0]->name ?? 'no role';
    }

    public function shop() {
        return $this->hasOne(Shop::class);
    }

    public function invite()
    {
        return $this->hasOne(Invitation::class);
    }


    public function moderatorShop() {
        return $this->hasOneThrough(Shop::class, Invitation::class,
            'user_id', 'id', 'id', 'shop_id');
    }

    public function moderatorShops() {
        return $this->hasManyThrough(Shop::class, Invitation::class,
            'user_id', 'id', 'id', 'shop_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }

    public function walletHasOne(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function socialProviders()
    {
        return $this->hasMany(SocialProvider::class,'user_id','id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class,'user_id');
    }

    public function deliverymanOrders()
    {
        return $this->hasMany(Order::class,'deliveryman_id');
    }

    public function orderDetails()
    {
        return $this->hasManyThrough(OrderDetail::class,Order::class);
    }

    public function point()
    {
        return $this->hasOne(UserPoint::class, 'user_id');
    }

    public function pointHistory(): HasMany
    {
        return $this->hasMany(PointHistory::class, 'user_id');
    }

    public function likes()
    {
        return $this->belongsToMany(Banner::class, Like::class);
    }

    public function deliveryManSetting(): HasOne
    {
        return $this->hasOne(DeliveryManSetting::class, 'user_id');
    }

    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class, NotificationUser::class)
            ->as('notification')
            ->withPivot('active');
    }


}
