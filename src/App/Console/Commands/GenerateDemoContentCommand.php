<?php
namespace VueFileManager\Subscription\App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Domain\Plans\Actions\StorePlanForPaymentServiceAction;

class GenerateDemoContentCommand extends Command
{
    public $signature = 'subscription:demo';

    public $description = 'Generate demo content';

    public function handle()
    {
        $this->call('subscription:demo-plans');
        $this->call('subscription:demo-subscriptions');
    }
}
