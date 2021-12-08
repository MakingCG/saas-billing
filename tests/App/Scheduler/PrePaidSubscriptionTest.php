<?php
namespace Tests\App\Scheduler;

use Carbon\Carbon;
use Tests\TestCase;
use Tests\Models\User;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use App\Scheduler\SettlePrePaidSubscriptionPeriodSchedule;
use VueFileManager\Subscription\Domain\Balances\Models\Balance;
use VueFileManager\Subscription\Domain\Plans\Models\PlanMeteredFeature;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class PrePaidSubscriptionTest extends TestCase
{
    /**
     * @test
     */
    public function it_settle_subscription_after_end_of_current_period()
    {
        $user = User::factory()
            ->create();

        $user->creditBalance(10.00, 'USD');

        $plan = Plan::factory()
            ->create([
                'type'     => 'metered',
                'currency' => 'USD',
            ]);

        PlanMeteredFeature::factory()
            ->hasTiers([
                'first_unit' => 1,
                'last_unit'  => null,
                'per_unit'   => 0.029,
                'flat_fee'   => 2.49,
            ])
            ->create([
                'plan_id'   => $plan->id,
                'key'       => 'bandwidth',
                'charge_by' => 'sum_of_usage',
            ]);

        PlanMeteredFeature::factory()
            ->hasTiers([
                'first_unit' => 1,
                'last_unit'  => null,
                'per_unit'   => 0.019,
                'flat_fee'   => 0,
            ])
            ->create([
                'plan_id'   => $plan->id,
                'key'       => 'storage',
                'charge_by' => 'maximum_usage',
            ]);

        $subscription = Subscription::factory()
            ->create([
                'type'       => 'pre-paid',
                'status'     => 'active',
                'plan_id'    => $plan->id,
                'user_id'    => $user->id,
                'renews_at'  => now(),
                'created_at' => now()->subDays(30),
                'ends_at'    => null,
            ]);

        foreach (range(1, 40) as $i) {
            // Travel by time
            $this->travel(-1)->days();

            // Record usages - 3.645 total
            $subscription->recordUsage('bandwidth', 1);
            $subscription->recordUsage('storage', 0.5);
        }

        Carbon::setTestNow('1. January 2022');

        resolve(SettlePrePaidSubscriptionPeriodSchedule::class)();

        $this->assertEquals(6.355, Balance::first()->balance);

        $this->assertDatabaseHas('transactions', [
            'user_id'   => $user->id,
            'plan_name' => $plan->name,
            'type'      => 'withdrawal',
            'status'    => 'completed',
            'currency'  => 'USD',
            'amount'    => 3.645,
            'driver'    => 'system',
            'reference' => null,
        ]);
    }
}
