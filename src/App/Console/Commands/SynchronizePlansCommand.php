<?php

namespace VueFileManager\Subscription\App\Console\Commands;

use ErrorException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use VueFileManager\Subscription\Domain\Plans\DTO\CreateFixedPlanData;
use VueFileManager\Subscription\Support\EngineManager;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;

class SynchronizePlansCommand extends Command
{
    public $signature = 'subscription:synchronize-plans';

    public $description = 'Synchronize plan data';

    public function __construct(
        private EngineManager $subscription,
    )
    {
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
        if (!$canSynchronizePlans) {
            $this->info('Nothing to synchronize.');
        }
    }

    public function synchronizePlans()
    {
        // Check if there are some uncreated plans
        Plan::where('status', 'active')
            ->where('type', 'fixed')
            ->cursor()
            ->each(function ($plan) {
                $driversToSynchronize = array_diff(
                    getActiveDrivers(), $plan->drivers()->pluck('driver')->toArray()
                );

                // Create missing plans
                foreach ($driversToSynchronize as $driver) {
                    try {
                        // Format message
                        $message = "Creating plan {$plan->name}... for $driver";

                        // Console log
                        $this->info($message);

                        // Error log
                        Log::info($message);

                        // Format data
                        $data = CreateFixedPlanData::fromArray([
                            'type'        => 'fixed',
                            'name'        => $plan->name,
                            'description' => $plan->description,
                            'interval'    => $plan->interval,
                            'amount'      => $plan->amount,
                            'currency'    => $plan->currency,
                            'features'    => [],
                        ]);

                        $newPlan = $this->subscription
                            ->driver($driver)
                            ->createFixedPlan($data);

                        // Attach driver plan id into internal plan record
                        $plan
                            ->drivers()
                            ->create([
                                'driver_plan_id' => $newPlan['id'],
                                'driver'         => $driver,
                            ]);
                    } catch (ErrorException $error) {

                        // Format message
                        $message = "Creating plan {$plan->name}... for $driver failed because of {$error->getMessage()}";

                        // Console log
                        $this->warn($message);

                        // Error log
                        Log::error($message);
                    }
                }
            });

        // Update plan via gateways api
        collect(getActiveDrivers())
            ->each(function ($driver) {
                Plan::where('status', 'active')
                    ->where('type', 'fixed')
                    ->cursor()
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
