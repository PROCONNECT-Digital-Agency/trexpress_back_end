<?php

namespace App\Traits;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property-read Transaction|null $transaction
 * @property-read Collection|Transaction[] $transactions
 * @property-read int $transactions_count
 */
trait Payable
{
    public function createTransaction($transaction): Model
    {
        return $this->transactions()->create([
            'payable_id' => $this->id,
            'price' => $transaction['price'],
            'user_id' => $transaction['user_id'] ?? auth('sanctum')->id(),
            'payment_sys_id' => $transaction['payment_sys_id'],
            'payment_trx_id' => $transaction['payment_trx_id'] ?? null,
            'note' => $transaction['note'] ?? '',
            'perform_time' => $transaction['perform_time'] ?? now(),
            'status_description' => $transaction['status_description'] ?? "Transaction in progress",
        ]);
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'payable');
    }
}
