<?php

namespace App\Models;

use Database\Factories\ShopTranslationFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ShopTranslation
 *
 * @property int $id
 * @property int $shop_id
 * @property string $locale
 * @property string $title
 * @property string|null $description
 * @property string|null $address
 * @method static Builder|ShopTranslation actualTranslation($lang)
 * @method static ShopTranslationFactory factory(...$parameters)
 * @method static Builder|ShopTranslation newModelQuery()
 * @method static Builder|ShopTranslation newQuery()
 * @method static Builder|ShopTranslation query()
 * @method static Builder|ShopTranslation whereAddress($value)
 * @method static Builder|ShopTranslation whereDescription($value)
 * @method static Builder|ShopTranslation whereId($value)
 * @method static Builder|ShopTranslation whereLocale($value)
 * @method static Builder|ShopTranslation whereShopId($value)
 * @method static Builder|ShopTranslation whereTitle($value)
 * @mixin Eloquent
 */
class ShopTranslation extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = [];

    public function scopeActualTranslation($query, $lang)
    {
        $lang = $lang ?? config('app.locale');
        return self::where('locale', $lang)->first() ? $query->where('locale', $lang) : $query->first();
    }
}
