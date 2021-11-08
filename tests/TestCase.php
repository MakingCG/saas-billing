<?php
namespace Tests;

use Laravel\Sanctum\SanctumServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use VueFileManager\Subscription\SubscriptionServiceProvider;

class TestCase extends Orchestra
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'VueFileManager\\Subscription\\Database\\Factories\\'.class_basename($modelName).'Factory'
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
        config()->set('subscription.credentials.stripe.secret', 'sk_test_51JOfUvF390KfNanYFwj2kWJ3ajogaSlsqKQiq6tB7O3C919u3pILB1bDEqMJcXKTTGDEGycp1zqd4qF0GF9Rgvax006wzXDggN');
        config()->set('subscription.credentials.paystack.secret', 'sk_test_5917169d64e9a5aa41f0b07eb43e3f143bc36f08');
        config()->set('subscription.credentials.paystack.public_key', 'pk_test_5d69324328b8904cdd3cad17ff60892c93abfe89');
    }
}
