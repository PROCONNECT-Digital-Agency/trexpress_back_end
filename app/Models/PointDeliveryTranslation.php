<?php

namespace App\Models;


use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProductTranslation
 *
 * @property int $id
 * @property int $product_id
 * @property string $locale
 * @property string $title
 * @property string|null $description
 * @method static Builder|PointDeliveryTranslation actualTranslation($lang)
 * @method static Builder|PointDeliveryTranslation newModelQuery()
 * @method static Builder|PointDeliveryTranslation newQuery()
 * @method static Builder|PointDeliveryTranslation query()
 * @method static Builder|PointDeliveryTranslation whereDescription($value)
 * @method static Builder|PointDeliveryTranslation whereId($value)
 * @method static Builder|PointDeliveryTranslation whereLocale($value)
 * @method static Builder|PointDeliveryTranslation whereTitle($value)
 * @mixin Eloquent
 */

class PointDeliveryTranslation extends Model
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
