<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CountryTranslation
 *
 * @method static Builder|CountryTranslation actualTranslation($lang)
 * @method static Builder|CountryTranslation newModelQuery()
 * @method static Builder|CountryTranslation newQuery()
 * @method static Builder|CountryTranslation query()
 * @mixin Eloquent
 */
class CountryTranslation extends Model
{
    use HasFactory;
    public $timestamps = false;

    public function scopeActualTranslation($query, $lang)
    {
        $lang = $lang ?? config('app.locale');
        return self::where('locale', $lang)->first() ? $query->where('locale', $lang) : $query->first();
    }
}
