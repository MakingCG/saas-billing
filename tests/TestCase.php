<?php
namespace Tests;

use Carbon\Carbon;
use Tests\Models\User;
use Laravel\Sanctum\SanctumServiceProvider;
use Illuminate\Support\Facades\Notification;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Kyslik\ColumnSortable\ColumnSortableServiceProvider;
use VueFileManager\Subscription\SubscriptionServiceProvider;

class TestCase extends Orchestra
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        Carbon::setTestNow('1. January 2022');

        $this->withoutExceptionHandling();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'VueFileManager\\Subscription\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ColumnSortableServiceProvider::class,
            SubscriptionServiceProvider::class,
            SanctumServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('auth.providers.users.model', User::class);

        config()->set('subscription.credentials.paypal.id', 'AX96WuhfdCT1bgwUo6uGtAefvdufFaKh0XVRTFUDoh_rTV7RpRGX8ipENIweybNY_fnp0MqqSIvZRp8t');
        config()->set('subscription.credentials.paypal.secret', 'EKJ7GV2zz5iSlvulPPb7kbqum3GN8Rb1kUCMnhhkmQEGftmVhUVz5_BpLwFvqcMH9v-qQmNhcaaRzsfl');
        config()->set('subscription.credentials.paypal.webhook_id', '5US38870H49278334');

        config()->set('subscription.credentials.paystack.secret', 'sk_test_5917169d64e9a5aa41f0b07eb43e3f143bc36f08');
        config()->set('subscription.credentials.paystack.public_key', 'pk_test_5d69324328b8904cdd3cad17ff60892c93abfe89');

        config()->set('subscription.credentials.stripe.secret', 'sk_test_51K1tczB9m4sTKy1qT03hg6jAP5CT0ERS7WJLY0FutMc45vqF1jxtqiAxdi9qXIEjEsp5rF0y4pHTCCwhafNgjZIT00CC4ZzW6N');
        config()->set('subscription.credentials.stripe.public_key', 'pk_test_51K1tczB9m4sTKy1qbG6iOguMBDJsGUBFjhQ5rOXphms6oqRtfduUIhxA4f7Vif0nCeHdn2oJ0c56OBBZjF1jfigb00ONWOAHDQ');
    }
}
