<?php

namespace App\Models;

use App\Traits\Likable;
use App\Traits\Loadable;
use Database\Factories\BannerFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Banner
 *
 * @property int $id
 * @property int|null $shop_id
 * @property string|null $url
 * @property string $type
 * @property array|null $products
 * @property string|null $img
 * @property int $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Collection<int, Gallery> $galleries
 * @property-read int|null $galleries_count
 * @property-read Collection<int, Like> $likes
 * @property-read int|null $likes_count
 * @property-read Shop|null $shop
 * @property-read BannerTranslation|null $translation
 * @property-read Collection<int, BannerTranslation> $translations
 * @property-read int|null $translations_count
 * @method static BannerFactory factory(...$parameters)
 * @method static Builder|Banner newModelQuery()
 * @method static Builder|Banner newQuery()
 * @method static Builder|Banner query()
 * @method static Builder|Banner whereActive($value)
 * @method static Builder|Banner whereCreatedAt($value)
 * @method static Builder|Banner whereDeletedAt($value)
 * @method static Builder|Banner whereId($value)
 * @method static Builder|Banner whereImg($value)
 * @method static Builder|Banner whereProducts($value)
 * @method static Builder|Banner whereShopId($value)
 * @method static Builder|Banner whereType($value)
 * @method static Builder|Banner whereUpdatedAt($value)
 * @method static Builder|Banner whereUrl($value)
 * @mixin Eloquent
 */
class Banner extends Model
{
    use HasFactory, Loadable, Likable;
    protected $guarded = [];

    protected $casts = [
        'products' => 'array'
    ];

    const TYPES = [
        'banner',
        'look',
    ];

    // Translations
    public function translations() {
        return $this->hasMany(BannerTranslation::class);
    }

    public function translation() {
        return $this->hasOne(BannerTranslation::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class)->withDefault();
    }
}
