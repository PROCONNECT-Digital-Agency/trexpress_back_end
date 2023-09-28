<?php

namespace App\Models;

use App\Traits\Loadable;
use Database\Factories\ReviewFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Review
 *
 * @property int $id
 * @property string $reviewable_type
 * @property int $reviewable_id
 * @property int $user_id
 * @property float $rating
 * @property string|null $comment
 * @property string|null $img
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $assignable_id
 * @property-read Collection<int, Gallery> $galleries
 * @property-read int|null $galleries_count
 * @property-read Model|Eloquent $reviewable
 * @property-read User $user
 * @method static ReviewFactory factory(...$parameters)
 * @method static Builder|Review newModelQuery()
 * @method static Builder|Review newQuery()
 * @method static Builder|Review query()
 * @method static Builder|Review whereAssignableId($value)
 * @method static Builder|Review whereComment($value)
 * @method static Builder|Review whereCreatedAt($value)
 * @method static Builder|Review whereId($value)
 * @method static Builder|Review whereImg($value)
 * @method static Builder|Review whereRating($value)
 * @method static Builder|Review whereReviewableId($value)
 * @method static Builder|Review whereReviewableType($value)
 * @method static Builder|Review whereUpdatedAt($value)
 * @method static Builder|Review whereUserId($value)
 * @mixin Eloquent
 */
class Review extends Model
{
    use HasFactory, Loadable;
    protected $fillable = ['user_id', 'rating', 'comment', 'img','assignable_id'];


    public function reviewable()
    {
        return $this->morphTo('reviewable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
