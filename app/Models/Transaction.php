<?php

namespace App\Models;

use App\Traits\SetCurrency;
use Database\Factories\TransactionFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Transaction
 *
 * @property int $id
 * @property string $payable_type
 * @property int $payable_id
 * @property float $price
 * @property int|null $user_id
 * @property int|null $payment_sys_id
 * @property string|null $payment_trx_id
 * @property string|null $note
 * @property string|null $perform_time
 * @property string|null $refund_time
 * @property string $status
 * @property string $status_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Model|Eloquent $payable
 * @property-read Payment|null $paymentSystem
 * @property-read User|null $user
 * @method static TransactionFactory factory(...$parameters)
 * @method static Builder|Transaction filter($array = [])
 * @method static Builder|Transaction newModelQuery()
 * @method static Builder|Transaction newQuery()
 * @method static Builder|Transaction query()
 * @method static Builder|Transaction whereCreatedAt($value)
 * @method static Builder|Transaction whereDeletedAt($value)
 * @method static Builder|Transaction whereId($value)
 * @method static Builder|Transaction whereNote($value)
 * @method static Builder|Transaction wherePayableId($value)
 * @method static Builder|Transaction wherePayableType($value)
 * @method static Builder|Transaction wherePaymentSysId($value)
 * @method static Builder|Transaction wherePaymentTrxId($value)
 * @method static Builder|Transaction wherePerformTime($value)
 * @method static Builder|Transaction wherePrice($value)
 * @method static Builder|Transaction whereRefundTime($value)
 * @method static Builder|Transaction whereStatus($value)
 * @method static Builder|Transaction whereStatusDescription($value)
 * @method static Builder|Transaction whereUpdatedAt($value)
 * @method static Builder|Transaction whereUserId($value)
 * @mixin Eloquent
 */
class Transaction extends Model
{
    use HasFactory,SetCurrency;
    protected $guarded = [];

    const PAID = 'paid';
    const UNPAID = 'unpaid';
    const PROGRESS = 'progress';
    const PENDING = 'pending';
    const CANCELED = 'canceled';
    const REFUND     = 'refund';

    public function payable()
    {
        return $this->morphTo('payable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentSystem(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_sys_id');
    }

    public function getPriceAttribute($value)
    {
        return $value * $this->currency();
    }

    public function scopeFilter($query, $array = [])
    {
        return $query
            ->when(isset($array['model']) && $array['model'] == 'orders' , function ($q) {
                $q->where(['payable_type' => OrderDetail::class]);
            })
            ->when(isset($array['model']) && $array['model'] == 'wallet' , function ($q) {
                $q->where(['payable_type' => Wallet::class]);
            })
            ->when(isset($array['user_id']), function ($q) use($array) {
                $q->where('user_id', $array['user_id']);
            })
            ->when(isset($array['status']), function ($q)  use($array)  {
                $q->where('status', $array['status']);
            });
    }
}
