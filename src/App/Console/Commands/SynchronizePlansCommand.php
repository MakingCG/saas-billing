<?php
namespace VueFileManager\Subscription\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use VueFileManager\Subscription\Support\EngineManager;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;

class SynchronizePlansCommand extends Command
{
    public $signature = 'subscription:synchronize-plans';

    public $description = 'Synchronize plan data';

    public function __construct(
        private EngineManager $subscription,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting synchronizing your plans...');

        $canSynchronizePlans = cache()->has('action.synchronize-plans');

        // Synchronize plans
        if ($canSynchronizePlans) {
            $this->synchronizePlans();
        }

        // Don't synchronize plans
        if (! $canSynchronizePlans) {
            $this->info('Nothing to synchronize.');
        }
    }

    public function synchronizePlans()
    {
        // Update plan via gateways api
        // TODO: check if there some uncreated plans
        // TODO: get only active plans
        collect(getActiveDrivers())
            ->each(function ($driver) {
                Plan::all()
                    ->each(function ($plan) use ($driver) {
                        $this->info("Synchronizing plans {$plan->name}...");

                        $this->subscription->driver($driver)->updatePlan($plan);
                    });
            });

        // Remove synchronize action
        cache()->delete('action.synchronize-plans');

        // Log last synchronization time
        Log::info('Last plan synchronization at: ' . now()->toString());

        // Show message in console
        $this->info('All your plans was synchronized, congratulations! 🥳🥳🥳');
    }
}
