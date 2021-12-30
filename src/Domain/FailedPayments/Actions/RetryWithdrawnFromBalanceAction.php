<?php
namespace VueFileManager\Subscription\Domain\FailedPayments\Actions;

use VueFileManager\Subscription\Domain\Credits\Exceptions\InsufficientBalanceException;
use VueFileManager\Subscription\Domain\Credits\Notifications\InsufficientBalanceNotification;

class RetryWithdrawnFromBalanceAction
{
    public function __invoke($user)
    {
        // Get failed payments and try pay it
        $user
            ->failedPayments()
            ->orderByDesc('amount')
            ->each(function ($failedPayment) use ($user) {
                try {
                    // Withdraw balance
                    $user->withdrawBalance($failedPayment->amount);

                    // Create transaction
                    $failedPayment->user->transactions()->create([
                        'type'      => 'withdrawal',
                        'status'    => 'completed',
                        'note'      => $failedPayment->note,
                        'currency'  => $failedPayment->currency,
                        'amount'    => $failedPayment->amount,
                        'driver'    => 'system',
                    ]);

                    // delete failed payment
                    $failedPayment->delete();
                } catch (InsufficientBalanceException $e) {
                    // Send notification
                    $user->notify(new InsufficientBalanceNotification());
                }
            });
    }
}
