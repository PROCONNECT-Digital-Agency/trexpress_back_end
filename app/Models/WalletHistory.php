<?php

namespace App\Models;

use Database\Factories\WalletHistoryFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\WalletHistory
 *
 * @property int $id
 * @property string $uuid
 * @property string $wallet_uuid
 * @property int|null $transaction_id
 * @property string $type
 * @property float $price
 * @property string|null $note
 * @property string $status
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $author
 * @property-read User|null $user
 * @property-read Wallet|null $wallet
 * @method static WalletHistoryFactory factory(...$parameters)
 * @method static Builder|WalletHistory newModelQuery()
 * @method static Builder|WalletHistory newQuery()
 * @method static Builder|WalletHistory query()
 * @method static Builder|WalletHistory whereCreatedAt($value)
 * @method static Builder|WalletHistory whereCreatedBy($value)
 * @method static Builder|WalletHistory whereId($value)
 * @method static Builder|WalletHistory whereNote($value)
 * @method static Builder|WalletHistory wherePrice($value)
 * @method static Builder|WalletHistory whereStatus($value)
 * @method static Builder|WalletHistory whereTransactionId($value)
 * @method static Builder|WalletHistory whereType($value)
 * @method static Builder|WalletHistory whereUpdatedAt($value)
 * @method static Builder|WalletHistory whereUuid($value)
 * @method static Builder|WalletHistory whereWalletUuid($value)
 * @mixin Eloquent
 */
class WalletHistory extends Model
{
    use HasFactory;
    protected $fillable = ['uuid', 'wallet_uuid', 'transaction_id', 'type', 'price', 'note', 'status', 'created_by'];

    const PROCESSED = 'processed';
    const PAID      = 'paid';
    const REJECTED  = 'rejected';
    const CANCELED  = 'canceled';

    const TYPES = [
        'processed',
        'paid',
        'rejected',
        'canceled'
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_uuid', 'uuid');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, Wallet::class,
            'uuid', 'id', 'wallet_uuid', 'user_id');
    }
}
