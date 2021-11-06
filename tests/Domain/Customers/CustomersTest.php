<?php
namespace Tests\Domain\Customers;

use Tests\TestCase;
use Tests\Models\User;
use Support\EngineManager;
use Illuminate\Database\Eloquent\Model;

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
    }
}
