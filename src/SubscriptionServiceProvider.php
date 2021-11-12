<?php
namespace VueFileManager\Subscription;

use Spatie\LaravelPackageTools\Package;
use Illuminate\Support\Facades\Validator;
use Illuminate\Console\Scheduling\Schedule;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use VueFileManager\Subscription\Support\EngineManager;
use VueFileManager\Subscription\App\Console\Commands\SetupDemoDataCommand;
use VueFileManager\Subscription\App\Console\Commands\SynchronizePlansCommand;

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
                SetupDemoDataCommand::class,
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

            $schedule->command('subscription:synchronize-plans')->everyFiveMinutes();
        });
    }
}
