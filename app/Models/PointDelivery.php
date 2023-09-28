<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\Point
 *
 * @property int $id
 * @property int|null $shop_id
 * @property array|null $location
 * @property float $keep_days
 * @property array|null $working_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Point newModelQuery()
 * @method static Builder|Point newQuery()
 * @method static Builder|Point query()
 * @method static Builder|Point whereActive($value)
 * @method static Builder|Point whereCreatedAt($value)
 * @method static Builder|Point whereId($value)
 * @method static Builder|Point wherePrice($value)
 * @method static Builder|Point whereShopId($value)
 * @method static Builder|Point whereType($value)
 * @method static Builder|Point whereUpdatedAt($value)
 * @method static Builder|Point whereValue($value)
 * @property-read PointDeliveryTranslation|null $translation
 * @property-read Collection<int, PointDeliveryTranslation> $translations
 * @property-read int|null $translations_count
 * @mixin Eloquent
 */
class PointDelivery extends Model
{
    use HasFactory;

    protected $fillable = ['shop_id','location','keep_days','working_time'];

    protected $casts = [
        'location' => 'array',
        'working_time' => 'array',
    ];

    // Translations
    public function translations(): HasMany
    {
        return $this->hasMany(PointDeliveryTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(PointDeliveryTranslation::class)->where('locale',app()->getLocale());
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
