<?php
namespace Tests\Domain\Customers;

use Tests\TestCase;
use Tests\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use VueFileManager\Subscription\Support\EngineManager;

class CustomersTest extends TestCase
{
    public Model $user;
    public EngineManager $subscription;

    public function setUp(): void
    {
        parent::setUp();

        $this->subscription = resolve(EngineManager::class);
        $this->user = User::factory()->create();
    }

    /**
     * @test
     */
    public function it_create_customer()
    {
        Http::fake([
            'https://api.paystack.co/customer' => Http::response([
                'status'  => true,
                'message' => 'Customer created',
                'data'    => [
                    'email'           => $this->user->email,
                    'integration'     => 100032,
                    'domain'          => 'test',
                    'customer_code'   => 'CUS_xnxdt6s1zg1f4nx',
                    'id'              => 1173,
                    'identified'      => false,
                    'identifications' => null,
                    'createdAt'       => '2016-03-29T20:03:09.584Z',
                    'updatedAt'       => '2016-03-29T20:03:09.584Z',
                ],
            ]),
        ]);

        $this->subscription->createCustomer([
            'id'      => $this->user->id,
            'email'   => $this->user->email,
            'name'    => 'John',
            'surname' => 'Doe',
            'phone'   => '+421 950 123 456',
        ]);

        $this->assertDatabaseHas('customers', [
            'user_id' => $this->user->id,
        ]);

        Http::assertSentCount(1);
    }

    /**
     * @test
     */
    public function it_update_customer()
    {
        Http::fake([
            'https://api.paystack.co/customer' => Http::response([
                'status'  => true,
                'message' => 'Customer created',
                'data'    => [
                    'email'           => $this->user->email,
                    'integration'     => 100032,
                    'domain'          => 'test',
                    'customer_code'   => 'CUS_xnxdt6s1zg1f4nx',
                    'id'              => 1173,
                    'identified'      => false,
                    'identifications' => null,
                    'createdAt'       => '2016-03-29T20:03:09.584Z',
                    'updatedAt'       => '2016-03-29T20:03:09.584Z',
                ],
            ]),
            'https://api.paystack.co/customer/*' => Http::response([
                'status'  => true,
                'message' => 'Customer updated',
                'data'    => [
                    'integration'     => 100032,
                    'first_name'      => 'BoJack',
                    'last_name'       => 'Horseman',
                    'email'           => $this->user->email,
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

        $this->subscription->createCustomer([
            'id'      => $this->user->id,
            'email'   => $this->user->email,
            'name'    => 'John',
            'surname' => 'Doe',
            'phone'   => '+421 950 123 456',
        ]);

        $this->assertDatabaseHas('customers', [
            'user_id' => $this->user->id,
        ]);

        $this->subscription->updateCustomer([
            'id'      => $this->user->id,
            'email'   => $this->user->email,
            'name'    => 'Jane',
            'surname' => 'Does',
            'phone'   => '+421 950 456 123',
        ]);

        Http::assertSentCount(2);
    }
}
