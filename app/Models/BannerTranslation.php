<?php

namespace App\Models;

use Database\Factories\BannerTranslationFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BannerTranslation
 *
 * @property int $id
 * @property int $banner_id
 * @property string $locale
 * @property string $title
 * @property string|null $description
 * @method static Builder|BannerTranslation actualTranslation($lang)
 * @method static BannerTranslationFactory factory(...$parameters)
 * @method static Builder|BannerTranslation newModelQuery()
 * @method static Builder|BannerTranslation newQuery()
 * @method static Builder|BannerTranslation query()
 * @method static Builder|BannerTranslation whereBannerId($value)
 * @method static Builder|BannerTranslation whereDescription($value)
 * @method static Builder|BannerTranslation whereId($value)
 * @method static Builder|BannerTranslation whereLocale($value)
 * @method static Builder|BannerTranslation whereTitle($value)
 * @mixin Eloquent
 */
class BannerTranslation extends Model
{
    use HasFactory;
    protected $fillable = ['locale', 'title', 'description'];
    public $timestamps = false;

    public function scopeActualTranslation($query, $lang)
    {
        $lang = $lang ?? config('app.locale');
        return self::where('locale', $lang)->first() ? $query->where('locale', $lang) : $query->first();
    }
}
