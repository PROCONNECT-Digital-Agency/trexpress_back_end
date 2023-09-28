<?php

namespace App\Models;

use Database\Factories\UnitTranslationFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UnitTranslation
 *
 * @property int $id
 * @property int $unit_id
 * @property string $locale
 * @property string $title
 * @method static Builder|UnitTranslation actualTranslation($lang)
 * @method static UnitTranslationFactory factory(...$parameters)
 * @method static Builder|UnitTranslation newModelQuery()
 * @method static Builder|UnitTranslation newQuery()
 * @method static Builder|UnitTranslation query()
 * @method static Builder|UnitTranslation whereId($value)
 * @method static Builder|UnitTranslation whereLocale($value)
 * @method static Builder|UnitTranslation whereTitle($value)
 * @method static Builder|UnitTranslation whereUnitId($value)
 * @mixin Eloquent
 */
class UnitTranslation extends Model
{
    use HasFactory;
    protected $fillable = ['locale', 'title'];
    public $timestamps = false;

    public function scopeActualTranslation($query, $lang)
    {
        $lang = $lang ?? config('app.locale');
        return self::where('locale', $lang)->first() ? $query->where('locale', $lang) : $query->first();
    }
}
