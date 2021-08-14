<?php

namespace Makingcg\Subscription;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use App\Console\Commands\SubscriptionCommand;

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
