<?php
namespace Tests\Domain\Balances;

use Tests\TestCase;
use Tests\Models\User;
use Illuminate\Database\Eloquent\Model;

class BalanceTest extends TestCase
{
    public Model $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()
            ->create();
    }

    /**
     * @test
     */
    public function it_credit_user_balance()
    {
        $this->user->creditBalance(50.00, 'USD');

        $this->assertDatabaseHas('balances', [
            'user_id'  => $this->user->id,
            'balance'  => 50.00,
            'currency' => 'USD',
        ]);
    }

    /**
     * @test
     */
    public function it_increment_user_balance()
    {
        $this->user->balance()->create([
            'currency' => 'USD',
            'balance'  => 50.00,
        ]);

        $this->user->creditBalance(10.49);

        $this->assertDatabaseHas('balances', [
            'user_id' => $this->user->id,
            'balance' => 60.49,
        ]);
    }

    /**
     * @test
     */
    public function it_withdraw_user_balance()
    {
        $this->user->balance()->create([
            'currency' => 'USD',
            'balance'  => 50.00,
        ]);

        $this->user->withdrawBalance(10.49);

        $this->assertDatabaseHas('balances', [
            'user_id' => $this->user->id,
            'balance' => 39.51,
        ]);
    }
}
