<?php
namespace VueFileManager\Subscription\Support\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use VueFileManager\Subscription\Domain\BillingAlerts\Models\BillingAlert;

class BillingAlertTriggeredEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public BillingAlert $alert
    ) {}
}
