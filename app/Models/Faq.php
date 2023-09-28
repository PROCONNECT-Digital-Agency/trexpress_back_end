<?php

namespace App\Models;

use Database\Factories\FaqFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Faq
 *
 * @property int $id
 * @property string $uuid
 * @property string|null $type
 * @property int $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read FaqTranslation|null $translation
 * @property-read Collection<int, FaqTranslation> $translations
 * @property-read int|null $translations_count
 * @method static FaqFactory factory(...$parameters)
 * @method static Builder|Faq newModelQuery()
 * @method static Builder|Faq newQuery()
 * @method static Builder|Faq query()
 * @method static Builder|Faq whereActive($value)
 * @method static Builder|Faq whereCreatedAt($value)
 * @method static Builder|Faq whereId($value)
 * @method static Builder|Faq whereType($value)
 * @method static Builder|Faq whereUpdatedAt($value)
 * @method static Builder|Faq whereUuid($value)
 * @mixin Eloquent
 */
class Faq extends Model
{
    use HasFactory;
    protected $fillable = ['uuid', 'type', 'active'];

    public function translations() {
        return $this->hasMany(FaqTranslation::class);
    }

    public function translation() {
        return $this->hasOne(FaqTranslation::class);
    }
}
