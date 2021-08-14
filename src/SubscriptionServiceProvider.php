<?php

namespace Makingcg\Subscription;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SubscriptionServiceProvider extends PackageServiceProvider
{
    public function registeringPackage()
    {
        $this->app->singleton(EngineManager::class, fn ($app) => new EngineManager($app));
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vuefilemanager-subscription')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_subscription_table');
    }
}
