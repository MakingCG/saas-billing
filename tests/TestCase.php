<?php

namespace Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Makingcg\Subscription\SubscriptionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Makingcg\\Subscription\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            SubscriptionServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('vuefilemanager-subscription.credentials.stripe.secret', 'sk_test_51GsACaCBETHMUxzVviYCrv0CeZMyWAOfBPe4uH5rkKJcJxrXhIciWQTr7UB1sgw9geoJMkNDVSWBQW36tuAsVznd00zhNHXhok');
        config()->set('vuefilemanager-subscription.credentials.flutter-wave.secret', 'FLWSECK_TEST-eef4e042b75bcef881694b26914b7f47-X');
    }
}
