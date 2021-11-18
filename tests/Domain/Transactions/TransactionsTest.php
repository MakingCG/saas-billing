<?php
namespace Tests\Domain\Transactions;

use Tests\TestCase;
use Tests\Models\User;

class TransactionsTest extends TestCase
{
    /**
     * @test
     */
    public function it_get_all_user_transactions()
    {
        $user = User::factory()
            ->hasTransactions(2)
            ->create();

        $response = $this
            ->actingAs($user)
            ->getJson('/api/subscription/transactions')
            ->assertOk();

        dd(json_decode($response->content(), true));
    }
}
