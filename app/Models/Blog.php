<?php

namespace App\Models;

use App\Traits\Loadable;
use App\Traits\Reviewable;
use Database\Factories\BlogFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Blog
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property int $type
 * @property string|null $published_at
 * @property int $active
 * @property string|null $img
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Collection<int, Gallery> $galleries
 * @property-read int|null $galleries_count
 * @property-read Review|null $review
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read BlogTranslation|null $translation
 * @property-read Collection<int, BlogTranslation> $translations
 * @property-read int|null $translations_count
 * @method static BlogFactory factory(...$parameters)
 * @method static Builder|Blog newModelQuery()
 * @method static Builder|Blog newQuery()
 * @method static Builder|Blog query()
 * @method static Builder|Blog whereActive($value)
 * @method static Builder|Blog whereCreatedAt($value)
 * @method static Builder|Blog whereDeletedAt($value)
 * @method static Builder|Blog whereId($value)
 * @method static Builder|Blog whereImg($value)
 * @method static Builder|Blog wherePublishedAt($value)
 * @method static Builder|Blog whereType($value)
 * @method static Builder|Blog whereUpdatedAt($value)
 * @method static Builder|Blog whereUserId($value)
 * @method static Builder|Blog whereUuid($value)
 * @mixin Eloquent
 */
class Blog extends Model
{
    use HasFactory, Loadable, Reviewable;

    protected $fillable = ['uuid', 'user_id', 'type', 'published_at', 'active', 'img'];

    const TYPES = [
        'blog' => 1,
        'notification' => 2,
    ];

    public function getTypeAttribute($value)
    {
        foreach (self::TYPES as $index => $type) {
            if ($type === $value){
                return $index;
            }
        }
    }

    public function translations() {
        return $this->hasMany(BlogTranslation::class);
    }

    public function translation() {
        return $this->hasOne(BlogTranslation::class);
    }
}
