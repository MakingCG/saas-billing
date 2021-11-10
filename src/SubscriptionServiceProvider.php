<?php
namespace VueFileManager\Subscription;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use VueFileManager\Subscription\Support\EngineManager;
use VueFileManager\Subscription\App\Console\Commands\SetupDemoDataCommand;

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
            ->hasCommand(SetupDemoDataCommand::class)
            ->hasRoutes([
                'api',
            ]);
    }

    public function bootingPackage()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
