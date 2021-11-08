<?php
namespace Tests\Domain\Subscriptions;

use Tests\TestCase;
use Domain\Customers\Models\Customer;

class SubscriptionsTest extends TestCase
{
    /**
     * @test
     */
    public function it_get_webhook_and_create_subscription()
    {
        $subscriptionId = 'SUB_vsyqdmlzble3uii';
        $customerId = 'CUS_xnxdt6s1zg1f4nx';

        // Create customer
        Customer::factory()->create([
            'driver_user_id' => $customerId,
            'driver'         => 'paystack',
        ]);

        // Send webhook
        $this->postJson('/api/subscription/webhooks', [
            'event' => 'subscription.create',
            'data'  => [
                'domain'            => 'test',
                'status'            => 'active',
                'subscription_code' => $subscriptionId,
                'amount'            => 50000,
                'cron_expression'   => '0 0 28 * *',
                'next_payment_date' => '2016-05-19T07:00:00.000Z',
                'open_invoice'      => null,
                'createdAt'         => '2016-03-20T00:23:24.000Z',
                'plan'              => [
                    'name'          => 'Monthly retainer',
                    'plan_code'     => 'PLN_gx2wn530m0i3w3m',
                    'description'   => null,
                    'amount'        => 50000,
                    'interval'      => 'monthly',
                    'send_invoices' => true,
                    'send_sms'      => true,
                    'currency'      => 'NGN',
                ],
                'authorization'     => [
                    'authorization_code' => 'AUTH_96xphygz',
                    'bin'                => '539983',
                    'last4'              => '7357',
                    'exp_month'          => '10',
                    'exp_year'           => '2017',
                    'card_type'          => 'MASTERCARD DEBIT',
                    'bank'               => 'GTBANK',
                    'country_code'       => 'NG',
                    'brand'              => 'MASTERCARD',
                    'account_name'       => 'BoJack Horseman',
                ],
                'customer'          => [
                    'first_name'    => 'BoJack',
                    'last_name'     => 'Horseman',
                    'email'         => 'bojack@horsinaround.com',
                    'customer_code' => $customerId,
                    'phone'         => '',
                    'metadata'      => [
                    ],
                    'risk_action'   => 'default',
                ],
                'created_at'        => '2016-10-01T10:59:59.000Z',
            ],
        ]);

        // Check if subscription was created
        $this->assertDatabaseHas('subscriptions', [
            'subscription_id' => $subscriptionId,
        ]);
    }
}
