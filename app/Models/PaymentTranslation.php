<?php

namespace App\Models;

use Database\Factories\PaymentTranslationFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PaymentTranslation
 *
 * @property int $id
 * @property int $payment_id
 * @property string $locale
 * @property string $title
 * @property string|null $client_title
 * @property string|null $secret_title
 * @method static Builder|PaymentTranslation actualTranslation($lang)
 * @method static PaymentTranslationFactory factory(...$parameters)
 * @method static Builder|PaymentTranslation newModelQuery()
 * @method static Builder|PaymentTranslation newQuery()
 * @method static Builder|PaymentTranslation query()
 * @method static Builder|PaymentTranslation whereClientTitle($value)
 * @method static Builder|PaymentTranslation whereId($value)
 * @method static Builder|PaymentTranslation whereLocale($value)
 * @method static Builder|PaymentTranslation wherePaymentId($value)
 * @method static Builder|PaymentTranslation whereSecretTitle($value)
 * @method static Builder|PaymentTranslation whereTitle($value)
 * @mixin Eloquent
 */
class PaymentTranslation extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function scopeActualTranslation($query, $lang)
    {
        $lang = $lang ?? config('app.locale');
        return self::where('locale', $lang)->first() ? $query->where('locale', $lang) : $query->first();
    }
}
