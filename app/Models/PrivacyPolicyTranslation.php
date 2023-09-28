<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PrivacyPolicyTranslation
 *
 * @property int $id
 * @property int $privacy_policy_id
 * @property string $title
 * @property string $description
 * @property string $locale
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static Builder|PrivacyPolicyTranslation actualTranslation($lang)
 * @method static Builder|PrivacyPolicyTranslation newModelQuery()
 * @method static Builder|PrivacyPolicyTranslation newQuery()
 * @method static Builder|PrivacyPolicyTranslation query()
 * @method static Builder|PrivacyPolicyTranslation whereCreatedAt($value)
 * @method static Builder|PrivacyPolicyTranslation whereDescription($value)
 * @method static Builder|PrivacyPolicyTranslation whereId($value)
 * @method static Builder|PrivacyPolicyTranslation whereLocale($value)
 * @method static Builder|PrivacyPolicyTranslation wherePrivacyPolicyId($value)
 * @method static Builder|PrivacyPolicyTranslation whereTitle($value)
 * @method static Builder|PrivacyPolicyTranslation whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PrivacyPolicyTranslation extends Model
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
