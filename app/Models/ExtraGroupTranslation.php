<?php

namespace App\Models;

use Database\Factories\ExtraGroupTranslationFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExtraGroupTranslation
 *
 * @property int $id
 * @property int $extra_group_id
 * @property string $locale
 * @property string $title
 * @method static Builder|ExtraGroupTranslation actualTranslation($lang)
 * @method static ExtraGroupTranslationFactory factory(...$parameters)
 * @method static Builder|ExtraGroupTranslation newModelQuery()
 * @method static Builder|ExtraGroupTranslation newQuery()
 * @method static Builder|ExtraGroupTranslation query()
 * @method static Builder|ExtraGroupTranslation whereExtraGroupId($value)
 * @method static Builder|ExtraGroupTranslation whereId($value)
 * @method static Builder|ExtraGroupTranslation whereLocale($value)
 * @method static Builder|ExtraGroupTranslation whereTitle($value)
 * @mixin Eloquent
 */
class ExtraGroupTranslation extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function scopeActualTranslation($query, $lang)
    {
        $lang = $lang ?? config('app.locale');
        return self::where('locale', $lang)->first() ? $query->where('locale', $lang) : $query->first();
    }
}
