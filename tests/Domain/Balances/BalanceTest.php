<?php
namespace Tests\Domain\Balances;

use Tests\TestCase;
use Tests\Models\User;
use Illuminate\Database\Eloquent\Model;
use VueFileManager\Subscription\Domain\Transactions\Models\Transaction;
use VueFileManager\Subscription\Domain\FailedPayments\Models\FailedPayment;
use VueFileManager\Subscription\Domain\Credits\Exceptions\InsufficientBalanceException;

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
    public function admin_credit_user_balance()
    {
        $admin = User::factory()
            ->create([
                'role' => 'admin',
            ]);

        $this->user->balance()->create([
            'currency' => 'USD',
            'amount'   => 50.00,
        ]);

        $this
            ->actingAs($admin)
            ->postJson("/api/subscriptions/admin/users/{$this->user->id}/credit", [
                'amount' => 20,
            ])
            ->assertNoContent();

        $this
            ->assertDatabaseHas('transactions', [
                'user_id'  => $this->user->id,
                'amount'   => 20.00,
                'currency' => 'USD',
                'status'   => 'completed',
                'type'     => 'credit',
                'driver'   => 'system',
            ])
            ->assertDatabaseHas('balances', [
                'user_id'  => $this->user->id,
                'amount'   => 70.00,
                'currency' => 'USD',
            ]);
    }

    /**
     * @test
     */
    public function it_credit_user_balance()
    {
        $this->user->creditBalance(50.00, 'USD');

        $this->assertDatabaseHas('balances', [
            'user_id'  => $this->user->id,
            'amount'   => 50.00,
            'currency' => 'USD',
        ]);
    }

    /**
     * @test
     */
    public function it_credit_balance_and_pay_user_failed_payment()
    {
        $this->user->creditBalance(5.00, 'USD');

        Transaction::factory()
            ->create([
                'user_id'  => $this->user->id,
                'amount'   => 10.25,
                'currency' => 'USD',
                'type'     => 'withdrawal',
                'status'   => 'error',
            ]);

        FailedPayment::factory()
            ->create([
                'user_id'        => $this->user->id,
                'amount'         => 10.25,
                'currency'       => 'USD',
            ]);

        $this->user->refresh();

        $this->user->creditBalance(50.00, 'USD');

        $this
            ->assertDatabaseHas('balances', [
                'user_id'  => $this->user->id,
                'amount'   => 44.75,
                'currency' => 'USD',
            ])
            ->assertDatabaseHas('transactions', [
                'user_id'  => $this->user->id,
                'type'     => 'withdrawal',
                'status'   => 'completed',
                'currency' => 'USD',
                'amount'   => 10.25,
            ])
            ->assertDatabaseCount('failed_payments', 0);
    }

    /**
     * @test
     */
    public function it_increment_user_balance()
    {
        $this->user->balance()->create([
            'currency' => 'USD',
            'amount'   => 50.00,
        ]);

        $this->user->creditBalance(10.49);

        $this->assertDatabaseHas('balances', [
            'user_id' => $this->user->id,
            'amount'  => 60.49,
        ]);
    }

    /**
     * @test
     */
    public function it_withdraw_user_balance()
    {
        $this->user->balance()->create([
            'currency' => 'USD',
            'amount'   => 50.00,
        ]);

        $this->user->withdrawBalance(10.49);

        $this->assertDatabaseHas('balances', [
            'user_id' => $this->user->id,
            'amount'  => 39.51,
        ]);
    }

    /**
     * @test
     */
    public function it_try_to_withdraw_more_than_current_user_balance()
    {
        $this->expectException(InsufficientBalanceException::class);

        $this->user->balance()->create([
            'currency' => 'USD',
            'amount'   => 10.00,
        ]);

        $this->user->withdrawBalance(20.00);
    }
}
