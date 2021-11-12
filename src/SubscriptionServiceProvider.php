<?php
namespace VueFileManager\Subscription;

use Spatie\LaravelPackageTools\Package;
use Illuminate\Support\Facades\Validator;
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

        Validator::extend('string_or_integer', fn ($attribute, $value) => is_string($value) || is_integer($value));
    }
}
