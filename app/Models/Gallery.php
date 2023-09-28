<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Gallery
 *
 * @property int $id
 * @property string $title
 * @property string $loadable_type
 * @property int $loadable_id
 * @property string|null $type
 * @property string|null $path
 * @property string|null $mime
 * @property string|null $size
 * @property-read Model|Eloquent $loadable
 * @method static Builder|Gallery newModelQuery()
 * @method static Builder|Gallery newQuery()
 * @method static Builder|Gallery query()
 * @method static Builder|Gallery whereId($value)
 * @method static Builder|Gallery whereLoadableId($value)
 * @method static Builder|Gallery whereLoadableType($value)
 * @method static Builder|Gallery whereMime($value)
 * @method static Builder|Gallery wherePath($value)
 * @method static Builder|Gallery whereSize($value)
 * @method static Builder|Gallery whereTitle($value)
 * @method static Builder|Gallery whereType($value)
 * @mixin Eloquent
 */
class Gallery extends Model
{
    use HasFactory;
    public $timestamps = false;

    const TYPES = [
        'banners', 'brands',
        'categories', 'languages',
        'shops', 'shops/logo', 'shops/background',
        'users', 'products', 'extras', 'reviews',
        'blogs', 'coupons', 'discounts','deliveryman/setting','referral','shop-tags'
    ];

    protected $fillable = ['title','loadable_type','loadable_id','type','path'];

    public function loadable() {
        return $this->morphTo('loadable');
    }
}
