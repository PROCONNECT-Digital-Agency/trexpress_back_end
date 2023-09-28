<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TermConditionTranslation
 *
 * @property int $id
 * @property int $term_condition_id
 * @property string $title
 * @property string $description
 * @property string $locale
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static Builder|TermConditionTranslation actualTranslation($lang)
 * @method static Builder|TermConditionTranslation newModelQuery()
 * @method static Builder|TermConditionTranslation newQuery()
 * @method static Builder|TermConditionTranslation query()
 * @method static Builder|TermConditionTranslation whereCreatedAt($value)
 * @method static Builder|TermConditionTranslation whereDescription($value)
 * @method static Builder|TermConditionTranslation whereId($value)
 * @method static Builder|TermConditionTranslation whereLocale($value)
 * @method static Builder|TermConditionTranslation whereTermConditionId($value)
 * @method static Builder|TermConditionTranslation whereTitle($value)
 * @method static Builder|TermConditionTranslation whereUpdatedAt($value)
 * @mixin Eloquent
 */
class TermConditionTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['title','description','locale'];

    public function scopeActualTranslation($query, $lang)
    {
        $lang = $lang ?? config('app.locale');
        return self::where('locale', $lang)->first() ? $query->where('locale', $lang) : $query->first();
    }
}
