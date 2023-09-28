<?php

namespace App\Models;

use Database\Factories\UnitFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Unit
 *
 * @property int $id
 * @property int $active
 * @property string $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read UnitTranslation|null $translation
 * @property-read Collection<int, UnitTranslation> $translations
 * @property-read int|null $translations_count
 * @method static UnitFactory factory(...$parameters)
 * @method static Builder|Unit newModelQuery()
 * @method static Builder|Unit newQuery()
 * @method static Builder|Unit query()
 * @method static Builder|Unit whereActive($value)
 * @method static Builder|Unit whereCreatedAt($value)
 * @method static Builder|Unit whereId($value)
 * @method static Builder|Unit wherePosition($value)
 * @method static Builder|Unit whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Unit extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Translations
    public function translations() {
        return $this->hasMany(UnitTranslation::class);
    }

    public function translation() {
        return $this->hasOne(UnitTranslation::class);
    }

}
