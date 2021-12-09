<?php
namespace Tests\App\Scheduler;

use Carbon\Carbon;
use Tests\TestCase;
use Tests\Models\User;
use Illuminate\Support\Facades\Notification;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use App\Scheduler\SettlePrePaidSubscriptionPeriodSchedule;
use VueFileManager\Subscription\Domain\Credits\Models\Balance;
use Domain\Credits\Notifications\InsufficientBalanceNotification;
use VueFileManager\Subscription\Domain\Plans\Models\PlanMeteredFeature;
use VueFileManager\Subscription\Domain\Transactions\Models\Transaction;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class SettlePrePaidSubscriptionPeriodTest extends TestCase
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
                'plan_id'            => $plan->id,
                'key'                => 'bandwidth',
                'aggregate_strategy' => 'sum_of_usage',
            ]);

        PlanMeteredFeature::factory()
            ->hasTiers([
                'first_unit' => 1,
                'last_unit'  => null,
                'per_unit'   => 0.019,
                'flat_fee'   => 0,
            ])
            ->create([
                'plan_id'            => $plan->id,
                'key'                => 'storage',
                'aggregate_strategy' => 'maximum_usage',
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

        // Reset time to current
        Carbon::setTestNow('1. January 2022');

        // Settle pre-paid subscription
        resolve(SettlePrePaidSubscriptionPeriodSchedule::class)();

        $this
            ->assertDatabaseHas('subscriptions', [
                'renews_at' => now()->addDays(30),
            ])
            ->assertDatabaseHas('transactions', [
                'user_id'   => $user->id,
                'type'      => 'withdrawal',
                'status'    => 'completed',
                'note'      => '01. Jan - 02. Dec',
                'currency'  => 'USD',
                'amount'    => 3.645,
                'driver'    => 'system',
                'reference' => null,
            ])
            ->assertEquals(6.355, Balance::first()->amount);
    }

    /**
     * @test
     */
    public function it_try_withdraw_from_insufficient_balance()
    {
        $user = User::factory()
            ->create();

        $user->creditBalance(20.00, 'USD');

        $plan = Plan::factory()
            ->create([
                'type'     => 'metered',
                'currency' => 'USD',
            ]);

        PlanMeteredFeature::factory()
            ->hasTiers([
                'first_unit' => 1,
                'last_unit'  => null,
                'per_unit'   => 0.90,
                'flat_fee'   => 2.49,
            ])
            ->create([
                'plan_id'            => $plan->id,
                'key'                => 'bandwidth',
                'aggregate_strategy' => 'sum_of_usage',
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

        foreach (range(1, 30) as $i) {
            // Travel by time
            $this->travel(-1)->days();

            // Record usages - 3.645 total
            $subscription->recordUsage('bandwidth', 1);
        }

        // Reset time to current
        Carbon::setTestNow('1. January 2022');

        // Settle pre-paid subscription
        resolve(SettlePrePaidSubscriptionPeriodSchedule::class)();

        $this
            ->assertDatabaseHas('subscriptions', [
                'renews_at' => now()->addDays(30),
            ])
            ->assertDatabaseHas('debts', [
                'amount'         => 29.49,
                'currency'       => 'USD',
                'user_id'        => $user->id,
                'transaction_id' => Transaction::first()->id,
            ])
            ->assertDatabaseHas('transactions', [
                'user_id'   => $user->id,
                'type'      => 'withdrawal',
                'status'    => 'error',
                'note'      => '01. Jan - 02. Dec',
                'currency'  => 'USD',
                'amount'    => 29.49,
                'driver'    => 'system',
                'reference' => null,
            ])
            ->assertEquals(20.00, Balance::first()->amount);

        Notification::assertSentTo($user, InsufficientBalanceNotification::class);
    }
}
