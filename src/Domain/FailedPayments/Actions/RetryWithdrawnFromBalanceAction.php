<?php
namespace Domain\FailedPayments\Actions;

use VueFileManager\Subscription\Domain\Credits\Exceptions\InsufficientBalanceException;
use VueFileManager\Subscription\Domain\Credits\Notifications\InsufficientBalanceNotification;

class RetryWithdrawnFromBalanceAction
{
    public function __invoke()
    {
        // Proceed if user has debt
        $this
            ->failedPayments()
            ->orderByDesc('amount')
            ->each(function ($failedPayment) {
                try {
                    // Withdraw balance
                    $this->withdrawBalance($failedPayment->amount);

                    // Update transaction
                    $failedPayment->transaction->update([
                        'status' => 'completed',
                    ]);

                    // delete failed payment
                    $failedPayment->delete();
                } catch (InsufficientBalanceException $e) {
                    $this->notify(new InsufficientBalanceNotification());
                }
            });
    }
}
