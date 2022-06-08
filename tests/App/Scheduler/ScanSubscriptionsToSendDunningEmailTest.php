<?php

namespace Tests\App\Scheduler;

use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\Models\User;
use Tests\TestCase;
use VueFileManager\Subscription\Domain\DunningEmails\Actions\ScanSubscriptionsToSendDunningEmailAction;
use VueFileManager\Subscription\Domain\DunningEmails\Notifications\DunningEmailToCoverAccountUsageNotification;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\Models\PlanMeteredFeature;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class ScanSubscriptionsToSendDunningEmailTest extends TestCase
{
    /**
     * @test
     */
    public function it_trigger_usage_bigger_than_balance_filter()
    {
        $user = User::factory()
            ->create([
                'created_at' => now()->subDays(60),
            ]);

        $user->creditBalance(5.00, 'USD');

        $plan = Plan::factory()
            ->create([
                'type'     => 'metered',
                'currency' => 'USD',
            ]);

        PlanMeteredFeature::factory()
            ->hasTiers([
                'first_unit' => 1,
                'last_unit'  => null,
                'per_unit'   => 0.29,
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
                'created_at' => now()->subDays(15),
                'ends_at'    => null,
            ]);

        foreach (range(1, 15) as $i) {
            // Travel by time
            $this->travel(-1)->days();

            // Record usages - 3.645 total
            $subscription->recordUsage('bandwidth', 1);
        }

        // Reset date
        Carbon::setTestNow('1. January 2022');

        // Run fraud mechanism
        resolve(ScanSubscriptionsToSendDunningEmailAction::class)();

        // Do email assertions
        Notification::assertSentToTimes($user, DunningEmailToCoverAccountUsageNotification::class);
        Notification::assertSentTo($user, DunningEmailToCoverAccountUsageNotification::class, function ($notification) use ($user) {
            $data = $notification->toMail($user);

            $this->assertStringContainsString("⚠️ You don't have sufficient funds in your account, please increase your account balance", $data->subject);
            $this->assertStringContainsString("We are happy you are using our service. To continue to using our service, please increase your funds for your account balance to cover your usage.", $data->render());

            return true;
        });

        $this->assertDatabaseHas('dunnings', [
            'user_id'   => $user->id,
            'reminders' => 2,
            'type'      => 'usage_bigger_than_balance',
        ]);
    }

    /**
     * @test
     */
    public function it_trigger_limit_usage_in_new_accounts()
    {
        $user = User::factory()
            ->create(['created_at' => now()->subDays(15)]);

        $plan = Plan::factory()
            ->create([
                'type'     => 'metered',
                'currency' => 'USD',
            ]);

        PlanMeteredFeature::factory()
            ->hasTiers([
                'first_unit' => 1,
                'last_unit'  => null,
                'per_unit'   => 0.29,
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
                'created_at' => now()->subDays(15),
                'ends_at'    => null,
            ]);

        foreach (range(1, 15) as $i) {
            // Travel by time
            $this->travel(-1)->days();

            // Record usages - 3.645 total
            $subscription->recordUsage('bandwidth', 1);
        }

        // Reset date
        Carbon::setTestNow('1. January 2022');

        // Run fraud mechanism
        resolve(ScanSubscriptionsToSendDunningEmailAction::class)();

        // Do email assertions
        Notification::assertSentToTimes($user, DunningEmailToCoverAccountUsageNotification::class);
        Notification::assertSentTo($user, DunningEmailToCoverAccountUsageNotification::class, function ($notification) use ($user) {
            $data = $notification->toMail($user);

            $this->assertStringContainsString('Please make first payment for your account to fund your usage.', $data->subject);
            $this->assertStringContainsString("We are happy you are using our service. To continue to using our service, please make first payment for your account balance to fund your usage.", $data->render());

            return true;
        });

        $this->assertDatabaseHas('dunnings', [
            'user_id'   => $user->id,
            'reminders' => 2,
            'type'      => 'limit_usage_in_new_accounts',
        ]);
    }
}
