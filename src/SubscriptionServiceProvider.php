<?php
namespace VueFileManager\Subscription;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use VueFileManager\Subscription\Support\EngineManager;

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
            ->hasRoutes([
                'api',
            ]);
    }

    public function bootingPackage()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
