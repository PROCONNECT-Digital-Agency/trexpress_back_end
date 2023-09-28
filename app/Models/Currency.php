<?php

namespace App\Models;

use Database\Factories\CurrencyFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\Currency
 *
 * @property int $id
 * @property string|null $symbol
 * @property string $title
 * @property float $rate
 * @property string $position
 * @property int $default
 * @property int $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $short_code
 * @property int|null $currency_code
 * @method static CurrencyFactory factory(...$parameters)
 * @method static Builder|Currency newModelQuery()
 * @method static Builder|Currency newQuery()
 * @method static Builder|Currency query()
 * @method static Builder|Currency whereActive($value)
 * @method static Builder|Currency whereCreatedAt($value)
 * @method static Builder|Currency whereCurrencyCode($value)
 * @method static Builder|Currency whereDefault($value)
 * @method static Builder|Currency whereId($value)
 * @method static Builder|Currency wherePosition($value)
 * @method static Builder|Currency whereRate($value)
 * @method static Builder|Currency whereShortCode($value)
 * @method static Builder|Currency whereSymbol($value)
 * @method static Builder|Currency whereTitle($value)
 * @method static Builder|Currency whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Currency extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'symbol', 'rate', 'active','position','default','active','short_code'];
    const TTL = 86400; // 1 day

    public static function currenciesList()
    {
        return Cache::remember('currencies-list', self::TTL, function (){
            return self::orderByDesc('default')->get();
        });
    }
}
