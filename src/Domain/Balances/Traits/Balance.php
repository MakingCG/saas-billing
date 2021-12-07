<?php
namespace Domain\Balances\Traits;

use ErrorException;
use Illuminate\Database\Eloquent\Relations\HasOne;
use VueFileManager\Subscription\Domain\Balances\Models\Balance as BalanceModel;

trait Balance
{
    public function balance(): HasOne
    {
        return $this->hasOne(BalanceModel::class, 'user_id', 'id');
    }

    /**
     * Increase user balance
     */
    public function creditBalance($balance, ?string $currency = null): void
    {
        // Create balance if not exist
        if (! $this->balance) {
            $this->balance()->create([
                'currency' => $currency,
                'balance'  => 0,
            ]);

            $this->refresh();
        }

        // Increment balance for new value
        $this->balance->increment('balance', $balance);
    }

    /**
     * Decrease user balance
     *
     * @throws ErrorException
     */
    public function withdrawBalance($balance): void
    {
        // Check if user has sufficient balance
        if ($this->balance->balance < $balance) {
            throw new ErrorException('There is not sufficient balance');
        }

        // Decrement balance
        $this->balance->decrement('balance', $balance);
    }
}
