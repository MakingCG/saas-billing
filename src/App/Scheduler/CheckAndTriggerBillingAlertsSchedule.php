<?php
namespace App\Scheduler;

use VueFileManager\Subscription\Domain\Usage\Actions\SumUsageForCurrentPeriodAction;
use VueFileManager\Subscription\Domain\BillingAlerts\Notifications\BillingAlertTriggered;
use VueFileManager\Subscription\Domain\BillingAlerts\Models\BillingAlert;

class CheckAndTriggerBillingAlertsSchedule
{
    public function __construct(
        public SumUsageForCurrentPeriodAction $sumUsageForCurrentPeriod
    ) {
    }

    public function __invoke()
    {
        BillingAlert::where('triggered', false)
            ->cursor()
            ->each(function ($alert) {
                // Get usage estimates
                $usageEstimates = ($this->sumUsageForCurrentPeriod)($alert->user->subscription);

                if ($usageEstimates->sum('amount') > $alert->amount) {
                    $alert->update([
                        'triggered' => true,
                    ]);

                    $alert->user->notify(new BillingAlertTriggered());
                }
            });
    }
}
