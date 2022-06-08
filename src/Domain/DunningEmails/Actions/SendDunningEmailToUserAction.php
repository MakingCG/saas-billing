<?php

namespace Domain\DunningEmails\Actions;

use VueFileManager\Subscription\Domain\DunningEmails\Models\Dunning;

class SendDunningEmailToUserAction
{
    public function __invoke(
        Dunning $dunning
    ): void {
        // Get notification class
        $dunningEmail = config('subscription.notifications.DunningEmailToCoverAccountUsageNotification');

        // Send notification
        $dunning->user->notify( new $dunningEmail(clone $dunning));

        // Increase reminder count
        if ($dunning->reminders < 3) {
            $dunning->increment('reminders');
        }
    }
}
