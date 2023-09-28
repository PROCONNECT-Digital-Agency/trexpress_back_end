<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserPoint
 *
 * @property int $id
 * @property int $user_id
 * @property float $price
 * @property-read User $user
 * @method static Builder|UserPoint newModelQuery()
 * @method static Builder|UserPoint newQuery()
 * @method static Builder|UserPoint query()
 * @method static Builder|UserPoint whereId($value)
 * @method static Builder|UserPoint wherePrice($value)
 * @method static Builder|UserPoint whereUserId($value)
 * @mixin Eloquent
 */
class UserPoint extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
