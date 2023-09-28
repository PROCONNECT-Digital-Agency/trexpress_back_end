<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\DeliveryTranslation
 *
 * @property int $id
 * @property int $delivery_id
 * @property string $locale
 * @property string $title
 * @method static Builder|DeliveryTranslation actualTranslation($lang)
 * @method static Builder|DeliveryTranslation newModelQuery()
 * @method static Builder|DeliveryTranslation newQuery()
 * @method static Builder|DeliveryTranslation query()
 * @method static Builder|DeliveryTranslation whereDeliveryId($value)
 * @method static Builder|DeliveryTranslation whereId($value)
 * @method static Builder|DeliveryTranslation whereLocale($value)
 * @method static Builder|DeliveryTranslation whereTitle($value)
 * @mixin Eloquent
 */
class DeliveryTranslation extends Model
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
