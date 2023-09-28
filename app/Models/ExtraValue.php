<?php

namespace App\Models;

use App\Traits\Loadable;
use Database\Factories\ExtraValueFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExtraValue
 *
 * @property int $id
 * @property int $extra_group_id
 * @property string $value
 * @property int $active
 * @property string|null $hex_color
 * @property-read Collection<int, Gallery> $galleries
 * @property-read int|null $galleries_count
 * @property-read ExtraGroup $group
 * @property-read Collection<int, Stock> $stocks
 * @property-read int|null $stocks_count
 * @method static ExtraValueFactory factory(...$parameters)
 * @method static Builder|ExtraValue newModelQuery()
 * @method static Builder|ExtraValue newQuery()
 * @method static Builder|ExtraValue query()
 * @method static Builder|ExtraValue whereActive($value)
 * @method static Builder|ExtraValue whereExtraGroupId($value)
 * @method static Builder|ExtraValue whereHexColor($value)
 * @method static Builder|ExtraValue whereId($value)
 * @method static Builder|ExtraValue whereValue($value)
 * @mixin Eloquent
 */
class ExtraValue extends Model
{
    use HasFactory, Loadable;
    protected $fillable = ['value', 'active','extra_group_id'];
    public $timestamps = false;

    public function group()
    {
        return $this->belongsTo(ExtraGroup::class, 'extra_group_id');
    }

    public function stocks()
    {
        return $this->belongsToMany(Stock::class, StockExtra::class);
    }

}
