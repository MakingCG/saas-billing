<?php
namespace VueFileManager\Subscription\Domain\Credits\Traits;

use VueFileManager\Subscription\Domain\Credits\Exceptions\InsufficientBalanceException;

trait CreditHelpers
{
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
