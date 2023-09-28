<?php

namespace App\Models;

use Database\Factories\StockExtraFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StockExtra
 *
 * @property int $id
 * @property int $stock_id
 * @property int $extra_value_id
 * @method static StockExtraFactory factory(...$parameters)
 * @method static Builder|StockExtra newModelQuery()
 * @method static Builder|StockExtra newQuery()
 * @method static Builder|StockExtra query()
 * @method static Builder|StockExtra whereExtraValueId($value)
 * @method static Builder|StockExtra whereId($value)
 * @method static Builder|StockExtra whereStockId($value)
 * @mixin Eloquent
 */
class StockExtra extends Model
{
    use HasFactory;
    public $timestamps = false;
}
