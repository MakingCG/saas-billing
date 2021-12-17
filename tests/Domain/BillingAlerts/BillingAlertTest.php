<?php
namespace Tests\Domain\BillingAlerts;

use Tests\TestCase;
use Tests\Models\User;

class BillingAlertTest extends TestCase
{
    /**
     * @test
     */
    public function it_store_billing_alert()
    {
        $user = User::factory()
            ->create();

        $this
            ->actingAs($user)
            ->postJson('/api/subscriptions/billing-alerts', [
                'amount' => 30,
            ])->assertCreated();

        $this->assertDatabaseHas('billing_alerts', [
            'user_id' => $user->id,
            'amount'  => 30,
        ]);
    }

    /**
     * @test
     */
    public function it_update_billing_alert()
    {
        $user = User::factory()
            ->create();

        $user->billingAlert()->create([
            'amount' => 20,
        ]);

        $this
            ->actingAs($user)
            ->patchJson("/api/subscriptions/billing-alerts/{$user->billingAlert->id}", [
                'amount' => 30,
            ])->assertNoContent();

        $this->assertDatabaseHas('billing_alerts', [
            'user_id' => $user->id,
            'amount'  => 30,
        ]);
    }

    /**
     * @test
     */
    public function it_delete_billing_alert()
    {
        $user = User::factory()
            ->create();

        $user->billingAlert()->create([
            'amount' => 20,
        ]);

        $this
            ->actingAs($user)
            ->delete("/api/subscriptions/billing-alerts/{$user->billingAlert->id}")
            ->assertNoContent();

        $this->assertModelMissing($user->billingAlert);
    }
}
