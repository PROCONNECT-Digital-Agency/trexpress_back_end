<?php

namespace App\Models;

use App\Traits\Loadable;
use Database\Factories\BrandFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Brand
 *
 * @property int $id
 * @property string $uuid
 * @property string $title
 * @property int $active
 * @property string|null $img
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Gallery> $galleries
 * @property-read int|null $galleries_count
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @method static BrandFactory factory(...$parameters)
 * @method static Builder|Brand filter($array)
 * @method static Builder|Brand newModelQuery()
 * @method static Builder|Brand newQuery()
 * @method static Builder|Brand query()
 * @method static Builder|Brand updatedDate($updatedDate)
 * @method static Builder|Brand whereActive($value)
 * @method static Builder|Brand whereCreatedAt($value)
 * @method static Builder|Brand whereId($value)
 * @method static Builder|Brand whereImg($value)
 * @method static Builder|Brand whereTitle($value)
 * @method static Builder|Brand whereUpdatedAt($value)
 * @method static Builder|Brand whereUuid($value)
 * @mixin Eloquent
 */
class Brand extends Model
{
    use HasFactory, Loadable;
    protected $guarded = [];

    public function products(){
        return $this->hasMany(Product::class);
    }

    public function scopeUpdatedDate($query, $updatedDate)
    {
        return $query->where('updated_at', '>', $updatedDate);
    }

    /* Filter Scope */
    public function scopeFilter($value, $array)
    {
        return $value
            ->when(isset($array['active']), function ($q) use ($array) {
                $q->whereActive($array['active']);
            });
    }

}
