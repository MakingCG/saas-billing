<?php
namespace Tests\Support\Miscellaneous\Stripe;

use Tests\TestCase;
use Tests\Models\User;
use Tests\Mocking\Stripe\CreateCustomerStripeMocksClass;
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

        $this
            ->actingAs($user)
            ->post('/api/subscriptions/stripe/checkout', [
                'planCode' => 'price_1K2GqrB9m4sTKy1qqq3VTaoi',
            ])
            ->assertCreated()
            ->assertJsonFragment([
                'url' => 'https://checkout.stripe.com/pay/cs_test_a1KLRKWfnXzfewSF8Yk',
            ]);
    }
}
