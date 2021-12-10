<?php
namespace VueFileManager\Subscription\Domain\Credits\Traits;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use VueFileManager\Subscription\Domain\Credits\Exceptions\InsufficientBalanceException;
use VueFileManager\Subscription\Domain\Credits\Models\Debt;
use VueFileManager\Subscription\Domain\Credits\Models\Balance;
use VueFileManager\Subscription\Domain\Credits\Notifications\InsufficientBalanceNotification;

trait Credits
{
    public function balance(): HasOne
    {
        return $this->hasOne(Balance::class, 'user_id', 'id');
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class, 'user_id', 'id');
    }

    /**
     * Increase user balance
     */
    public function creditBalance(float $credit, ?string $currency = null): void
    {
        // Create balance record if not exist
        if ($this->balance()->doesntExist()) {
            $this
                ->balance()
                ->create([
                    'currency' => $currency,
                ])
                ->refresh();
        }

        // Increment balance for new value
        $this->balance()->increment('amount', $credit);

        // Proceed if user has debt
        if ($this->debts()->exists()) {
            $this
                ->debts()
                ->orderByDesc('amount')
                ->each(function ($debt) {
                    try {
                        // Withdraw balance
                        $this->withdrawBalance($debt->amount);

                        // Update transaction
                        $debt->transaction->update([
                            'status' => 'completed',
                        ]);

                        // delete debt
                        $debt->delete();
                    } catch (InsufficientBalanceException $e) {
                        $this->notify(new InsufficientBalanceNotification());
                    }
                });
        }
    }

    /**
     * Decrease user balance
     *
     * @throws InsufficientBalanceException
     */
    public function withdrawBalance(float $balance): void
    {
        // Check if user has sufficient balance
        if ($this->balance->amount < $balance) {
            throw new InsufficientBalanceException();
        }

        // Decrement balance
        $this->balance()->decrement('amount', $balance);
    }
}
