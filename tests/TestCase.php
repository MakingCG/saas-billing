<?php

namespace Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Sanctum\SanctumServiceProvider;
use Makingcg\Subscription\SubscriptionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Makingcg\\Subscription\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            SubscriptionServiceProvider::class,
            SanctumServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('vuefilemanager-subscription.credentials.stripe.secret', 'sk_test_51JOfUvF390KfNanYFwj2kWJ3ajogaSlsqKQiq6tB7O3C919u3pILB1bDEqMJcXKTTGDEGycp1zqd4qF0GF9Rgvax006wzXDggN');
        config()->set('vuefilemanager-subscription.credentials.flutter-wave.secret', 'FLWSECK_TEST-eef4e042b75bcef881694b26914b7f47-X');
    }
}
