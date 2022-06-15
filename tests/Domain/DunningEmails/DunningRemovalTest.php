<?php
namespace Tests\Domain\DunningEmails;

use Tests\Models\User;
use Tests\TestCase;
use VueFileManager\Subscription\Domain\DunningEmails\Models\Dunning;

class DunningRemovalTest extends TestCase
{
    /**
     * @test
     */
    public function it_remove_dunning_after_paystack_charge_complete()
    {
        $user = User::factory()
            ->create([
                'email' => 'howdy@hi5ve.digital',
            ]);

        Dunning::factory()
            ->createOneQuietly([
                'user_id'  => $user->id,
                'type'     => 'usage_bigger_than_balance',
                'sequence' => 3,
            ]);

        $this
            ->withServerVariables([
                'REMOTE_ADDR' => '52.31.139.75',
            ])
            ->postJson('/api/subscriptions/paystack/webhooks', [
                'event'         => 'charge.success',
                'data'          => [
                    'id'                   => 1454595684,
                    'domain'               => 'test',
                    'status'               => 'success',
                    'reference'            => 'gD2au4Jyuc',
                    'amount'               => 1000,
                    'message'              => null,
                    'gateway_response'     => 'Successful',
                    'paid_at'              => '2021-11-18T06:42:13.000Z',
                    'created_at'           => '2021-11-18T06:42:08.000Z',
                    'channel'              => 'card',
                    'currency'             => 'ZAR',
                    'ip_address'           => '88.212.40.149',
                    'metadata'             => [
                        'referrer' => 'http://localhost:8000/user/profile',
                    ],
                    'log'                  => [
                        'start_time' => 1637217729,
                        'time_spent' => 4,
                        'attempts'   => 1,
                        'errors'     => 0,
                        'success'    => true,
                        'mobile'     => false,
                        'input'      => [],
                        'history'    => [
                            [
                                'type'    => 'action',
                                'message' => 'Attempted to pay with card',
                                'time'    => 4,
                            ],
                            [
                                'type'    => 'success',
                                'message' => 'Successfully paid with card',
                                'time'    => 4,
                            ],
                        ],
                    ],
                    'fees'                 => 129,
                    'fees_split'           => null,
                    'authorization'        => [
                        'authorization_code'           => 'AUTH_794qfjtskk',
                        'bin'                          => '408408',
                        'last4'                        => '4081',
                        'exp_month'                    => '12',
                        'exp_year'                     => '2030',
                        'channel'                      => 'card',
                        'card_type'                    => 'visa',
                        'bank'                         => 'TEST BANK',
                        'country_code'                 => 'ZA',
                        'brand'                        => 'visa',
                        'reusable'                     => true,
                        'signature'                    => 'SIG_XtQmL8nBieEY6wPi0zzP',
                        'account_name'                 => null,
                        'receiver_bank_account_number' => null,
                        'receiver_bank'                => null, ],
                    'customer'             => [
                        'id'                         => 61682919,
                        'first_name'                 => null,
                        'last_name'                  => null,
                        'email'                      => 'howdy@hi5ve.digital',
                        'customer_code'              => 'CUS_eb0h9lcy7ustcfw',
                        'phone'                      => null,
                        'metadata'                   => null,
                        'risk_action'                => 'default',
                        'international_format_phone' => null,
                    ],
                    'plan'                 => [],
                    'subaccount'           => [],
                    'split'                => [],
                    'order_id'             => null,
                    'paidAt'               => '2021-11-18T06:42:13.000Z',
                    'requested_amount'     => 1000,
                    'pos_transaction_data' => null,
                    'source'               => [
                        'source'     => 'checkout',
                        'identifier' => null,
                        'event_type' => 'web',
                    ],
                    'fees_breakdown'       => null,
                ],
                'order'         => null,
                'business_name' => 'VueFileManager',
            ])
            ->assertOk();

        $this
            ->assertDatabaseMissing('dunnings', [
                'user_id'  => $user->id,
            ])
            ->assertDatabaseHas('balances', [
                'user_id'  => $user->id,
                'amount'   => 10.00,
                'currency' => 'ZAR',
            ])
            ->assertDatabaseHas('transactions', [
                'user_id'   => $user->id,
                'type'      => 'charge',
                'status'    => 'completed',
                'currency'  => 'ZAR',
                'amount'    => 10,
                'driver'    => 'paystack',
                'reference' => 'gD2au4Jyuc',
            ]);
    }
    /**
     * @test
     */
    public function it_remove_dunning_after_paypal_charge_complete()
    {
        $user = User::factory()
            ->create([
                'email' => 'howdy@hi5ve.digital',
            ]);

        Dunning::factory()
            ->createOneQuietly([
                'user_id'  => $user->id,
                'type'     => 'usage_bigger_than_balance',
                'sequence' => 3,
            ]);

        // TODO: finish test
    }
}
