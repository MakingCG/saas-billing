<?php

namespace Makingcg\Subscription;

use App\Console\Commands\SubscriptionCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SubscriptionServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('vuefilemanager-subscription')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_subscription_table')
            ->hasCommand(SubscriptionCommand::class);
    }
}
