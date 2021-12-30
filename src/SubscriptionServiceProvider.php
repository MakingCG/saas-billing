<?php
namespace VueFileManager\Subscription;

use Spatie\LaravelPackageTools\Package;
use Illuminate\Support\Facades\Validator;
use Illuminate\Console\Scheduling\Schedule;
use App\Scheduler\HaltExpiredSubscriptionsSchedule;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use VueFileManager\Subscription\Support\EngineManager;
use App\Scheduler\SettlePrePaidSubscriptionPeriodSchedule;
use Domain\FailedPayments\Actions\RetryChargeFromPaymentCardAction;
use VueFileManager\Subscription\App\Console\Commands\SynchronizePlansCommand;
use VueFileManager\Subscription\App\Console\Commands\GenerateDemoPlansCommand;
use VueFileManager\Subscription\App\Console\Commands\GenerateDemoContentCommand;
use VueFileManager\Subscription\App\Console\Commands\GenerateDemoSubscriptionsCommand;

class SubscriptionServiceProvider extends PackageServiceProvider
{
    public function registeringPackage()
    {
        $this->app->singleton(EngineManager::class, fn ($app) => new EngineManager($app));
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('subscription')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommands([
                GenerateDemoSubscriptionsCommand::class,
                GenerateDemoContentCommand::class,
                GenerateDemoPlansCommand::class,
                SynchronizePlansCommand::class,
            ])
            ->hasRoutes([
                'api',
            ]);
    }

    public function bootingPackage()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register validator
        Validator::extend('string_or_integer', fn ($attribute, $value) => is_string($value) || is_integer($value));

        // Schedule background operations
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Synchronize plans with services if there were some changes
            $schedule->command('subscription:synchronize-plans')
                ->everyFiveMinutes();

            // Halt expired subscriptions
            $schedule->call(HaltExpiredSubscriptionsSchedule::class)
                ->daily();

            // Settle pre-paid subscriptions
            $schedule->call(SettlePrePaidSubscriptionPeriodSchedule::class)
                ->daily();

            // Try failed credit card charge again
            $schedule->call(RetryChargeFromPaymentCardAction::class)
                ->daily();
        });
    }
}
