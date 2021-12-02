<?php
namespace Tests\Mocking\PayStack;

use Illuminate\Support\Facades\Http;

class UpdateCustomerPaystackMocksClass
{
    public function __invoke($user)
    {
        return Http::fake([
            'https://api.paystack.co/customer/*' => Http::response([
                'status'  => true,
                'message' => 'Customer updated',
                'data'    => [
                    'integration'     => 100032,
                    'first_name'      => 'BoJack',
                    'last_name'       => 'Horseman',
                    'email'           => $user->email,
                    'phone'           => null,
                    'metadata'        => [
                        'photos' => [
                            [
                                'type'      => 'twitter',
                                'typeId'    => 'twitter',
                                'typeName'  => 'Twitter',
                                'url'       => 'https://d2ojpxxtu63wzl.cloudfront.net/static/61b1a0a1d4dda2c9fe9e165fed07f812_a722ae7148870cc2e33465d1807dfdc6efca33ad2c4e1f8943a79eead3c21311',
                                'isPrimary' => true,
                            ],
                        ],
                    ],
                    'identified'      => false,
                    'identifications' => null,
                    'domain'          => 'test',
                    'customer_code'   => 'CUS_xnxdt6s1zg1f4nx',
                    'id'              => 1173,
                    'transactions'    => [],
                    'subscriptions'   => [],
                    'authorizations'  => [],
                    'createdAt'       => '2016-03-29T20:03:09.000Z',
                    'updatedAt'       => '2016-03-29T20:03:10.000Z',
                ],
            ]),
        ]);
    }
}
