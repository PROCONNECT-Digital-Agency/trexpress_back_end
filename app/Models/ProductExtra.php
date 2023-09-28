<?php

namespace App\Models;

use Database\Factories\ProductExtraFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProductExtra
 *
 * @property int $id
 * @property int $product_id
 * @property int $extra_group_id
 * @property-read ExtraGroup $extras
 * @method static ProductExtraFactory factory(...$parameters)
 * @method static Builder|ProductExtra newModelQuery()
 * @method static Builder|ProductExtra newQuery()
 * @method static Builder|ProductExtra query()
 * @method static Builder|ProductExtra whereExtraGroupId($value)
 * @method static Builder|ProductExtra whereId($value)
 * @method static Builder|ProductExtra whereProductId($value)
 * @mixin Eloquent
 */
class ProductExtra extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['extra_value_id', 'price'];

    public function extras(){
        return $this->belongsTo(ExtraGroup::class, 'extra_group_id');
    }
}
