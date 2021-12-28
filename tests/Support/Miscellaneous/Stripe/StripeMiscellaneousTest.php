<?php
namespace Tests\Support\Miscellaneous\Stripe;

use Tests\TestCase;
use Tests\Models\User;
use Tests\Mocking\Stripe\GetPlanStripeMocksClass;
use Tests\Mocking\Stripe\CreateCustomerStripeMocksClass;
use Tests\Mocking\Stripe\CreateSetupIntentStripeMockClass;
use Tests\Mocking\Stripe\CreateCheckoutSessionStripeMockClass;

class StripeMiscellaneousTest extends TestCase
{
    /**
     * @test
     */
    public function it_create_stripe_checkout_session()
    {
        $user = User::factory()
            ->create();

        resolve(CreateCustomerStripeMocksClass::class)($user);
        resolve(CreateCheckoutSessionStripeMockClass::class)();
        resolve(GetPlanStripeMocksClass::class)();

        $this
            ->actingAs($user)
            ->post('/api/stripe/checkout', [
                'planCode' => 'prod_HKL7vHEYRSC4Ur',
            ])
            ->assertCreated()
            ->assertJsonFragment([
                'url' => 'https://checkout.stripe.com/pay/cs_test_a1KLRKWfnXzfewSF8Yk',
            ]);
    }

    /**
     * @test
     */
    public function it_create_stripe_setup_intent()
    {
        $user = User::factory()
            ->create();

        resolve(CreateCustomerStripeMocksClass::class)($user);
        resolve(CreateSetupIntentStripeMockClass::class)();

        $this
            ->actingAs($user)
            ->getJson('/api/stripe/setup-intent')
            ->assertCreated()
            ->assertJsonFragment([
                'client_secret' => 'seti_1KBhcBB9m4sTKy1q52kDOR9I_secret_KrQTiN7KGYtLKAjj2BybzRpgUcpizMq',
            ]);
    }
}
