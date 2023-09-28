<?php

namespace App\Models;

use Database\Factories\TermConditionFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\TermCondition
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TermConditionTranslation|null $translation
 * @property-read Collection<int, TermConditionTranslation> $translations
 * @property-read int|null $translations_count
 * @method static TermConditionFactory factory(...$parameters)
 * @method static Builder|TermCondition newModelQuery()
 * @method static Builder|TermCondition newQuery()
 * @method static Builder|TermCondition query()
 * @method static Builder|TermCondition whereCreatedAt($value)
 * @method static Builder|TermCondition whereId($value)
 * @method static Builder|TermCondition whereUpdatedAt($value)
 * @mixin Eloquent
 */
class TermCondition extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Translations
    public function translations() {
        return $this->hasMany(TermConditionTranslation::class);
    }

    public function translation() {
        return $this->hasOne(TermConditionTranslation::class);
    }

}
