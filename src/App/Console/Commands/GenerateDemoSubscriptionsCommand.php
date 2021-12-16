<?php
namespace VueFileManager\Subscription\App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class GenerateDemoSubscriptionsCommand extends Command
{
    public $signature = 'subscription:demo-subscriptions {type=fixed}';

    public $description = 'Generate demo subscriptions with their transactions';

    public function handle()
    {
        if ($this->argument('type') === 'metered') {
            $this->info('Setting up new pre-paid subscriptions data...');

            $this->generateMeteredSubscription();
        }

        if ($this->argument('type') === 'fixed') {
            $this->info('Setting up new fixed subscriptions data...');

            $this->generateFixedSubscription();
        }

        $this->after();

        $this->info('Everything is done, congratulations! 🥳🥳🥳');
    }

    public function generateMeteredSubscription(): void
    {
        $howdy = config('auth.providers.users.model')::where('email', 'howdy@hi5ve.digital')
            ->first();

        $plan = Plan::where('name', 'Pay as You Go')
            ->first();

        $this->info("Storing {$plan->name} for {$howdy->email}...");

        $subscription = Subscription::create([
            'user_id'    => $howdy->id,
            'type'       => 'pre-paid',
            'plan_id'    => $plan->id,
            'name'       => $plan->name,
            'status'     => 'active',
            'renews_at'  => now()->addDays(16),
            'created_at' => now()->subDays(14),
            'updated_at' => now()->subDays(14),
        ]);

        // Log fake usage
        foreach (range(1, 31) as $item) {
            $this->info('Logging fake bandwidth usage...');

            $bandwidthFeature = $plan
                ->meteredFeatures()
                ->where('key', 'bandwidth')
                ->first();

            $subscription->usages()->create([
                'metered_feature_id' => $bandwidthFeature->id,
                'quantity'           => random_int(111, 999),
                'created_at'         => now()->subDays($item),
            ]);

            $this->info('Logging fake storage usage...');

            $storageFeature = $plan
                ->meteredFeatures()
                ->where('key', 'storage')
                ->first();

            $subscription->usages()->create([
                'metered_feature_id' => $storageFeature->id,
                'quantity'           => random_int(1111, 3999),
                'created_at'         => now()->subDays($item),
            ]);
        }

        $this->info("Storing transactions for {$howdy->email}...");

        collect([
            [
                'type'       => 'withdrawal',
                'created_at' => now()->subDays(2),
                'amount'     => 12.59,
                'note'       => now()->subDays(2)->format('d. M') . ' - ' . now()->subDays(32)->format('d. M'),
                'driver'     => 'system',
            ],
            [
                'type'       => 'credit',
                'created_at' => now()->subDays(26 * 1),
                'note'       => 'Bonus',
                'amount'     => 12.00,
                'driver'     => 'system',
            ],
            [
                'type'       => 'withdrawal',
                'created_at' => now()->subDays(26 * 1),
                'note'       => now()->subDays(26 * 1)->format('d. M') . ' - ' . now()->subDays(30 + 26 * 1)->format('d. M'),
                'amount'     => 2.38,
                'driver'     => 'system',
            ],
            [
                'type'       => 'withdrawal',
                'created_at' => now()->subDays(26 * 2),
                'note'       => now()->subDays(26 * 2)->format('d. M') . ' - ' . now()->subDays(30 + 26 * 2)->format('d. M'),
                'amount'     => 5.12,
                'driver'     => 'system',
            ],
            [
                'type'       => 'withdrawal',
                'created_at' => now()->subDays(26 * 3),
                'note'       => now()->subDays(26 * 3)->format('d. M') . ' - ' . now()->subDays(30 + 26 * 3)->format('d. M'),
                'amount'     => 3.89,
                'driver'     => 'system',
            ],
            [
                'type'       => 'withdrawal',
                'created_at' => now()->subDays(26 * 4),
                'note'       => now()->subDays(26 * 4)->format('d. M') . ' - ' . now()->subDays(30 + 26 * 4)->format('d. M'),
                'amount'     => 7.42,
                'driver'     => 'system',
            ],
            [
                'type'       => 'charge',
                'created_at' => now()->subDays(26 * 5),
                'note'       => 'Account Fund',
                'amount'     => 50.00,
                'driver'     => 'paypal',
            ],
        ])->each(
            fn ($transaction) => $howdy->transactions()->create([
                'type'       => $transaction['type'],
                'status'     => 'completed',
                'note'       => $transaction['note'],
                'currency'   => $plan->currency,
                'driver'     => $transaction['driver'],
                'amount'     => $transaction['amount'],
                'created_at' => $transaction['created_at'],
                'reference'  => Str::random(12),
            ])
        );

        $howdy->balance()->create([
            'currency' => 'USD',
            'amount'   => 30.60,
        ]);
    }

    public function generateFixedSubscription(): void
    {
        $howdy = config('auth.providers.users.model')::where('email', 'howdy@hi5ve.digital')
            ->first();

        $alice = config('auth.providers.users.model')::where('email', 'alice@hi5ve.digital')
            ->first();

        $johan = config('auth.providers.users.model')::where('email', 'johan@hi5ve.digital')
            ->first();

        $professionalPackPlan = Plan::where('name', 'Professional Pack')
            ->where('interval', 'month')
            ->first();

        $businessPackPlan = Plan::where('name', 'Business Pack')
            ->where('interval', 'month')
            ->first();

        $this->info("Storing {$professionalPackPlan->name} for {$howdy->email}...");

        $howdySubscription = $howdy->subscription()->create([
            'plan_id'    => $professionalPackPlan->id,
            'name'       => $professionalPackPlan->name,
            'status'     => 'active',
            'created_at' => now()->subDays(14),
            'updated_at' => now()->subDays(14),
        ]);

        $this->info("Storing {$businessPackPlan->name} for {$alice->email}...");

        $aliceSubscription = $alice->subscription()->create([
            'plan_id'    => $businessPackPlan->id,
            'name'       => $businessPackPlan->name,
            'status'     => 'active',
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(9),
        ]);

        $this->info("Storing {$professionalPackPlan->name} for {$johan->email}...");

        $johanSubscription = $johan->subscription()->create([
            'plan_id'    => $professionalPackPlan->id,
            'name'       => $professionalPackPlan->name,
            'status'     => 'cancelled',
            'ends_at'    => now()->addDays(18),
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        $this->info("Storing transactions for {$howdy->email}...");

        collect([
            ['created_at' => now()->subDays(2)],
            ['created_at' => now()->subDays(26)],
            ['created_at' => now()->subDays(26 * 2)],
            ['created_at' => now()->subDays(26 * 3)],
            ['created_at' => now()->subDays(26 * 4)],
            ['created_at' => now()->subDays(26 * 5)],
        ])->each(
            fn ($transaction) => $howdy->transactions()->create([
                'status'     => 'completed',
                'note'       => $professionalPackPlan->name,
                'currency'   => $professionalPackPlan->currency,
                'amount'     => $professionalPackPlan->amount,
                'driver'     => 'paypal',
                'created_at' => $transaction['created_at'],
                'reference'  => Str::random(12),
            ])
        );

        $this->info("Storing transactions for {$johan->email}...");

        collect([
            ['created_at' => now()->subDay()],
            ['created_at' => now()->subDays(29)],
            ['created_at' => now()->subDays(29 * 2)],
            ['created_at' => now()->subDays(29 * 3)],
        ])->each(
            fn ($transaction) => $johan->transactions()->create([
                'status'     => 'completed',
                'note'       => $professionalPackPlan->name,
                'currency'   => $professionalPackPlan->currency,
                'amount'     => $professionalPackPlan->amount,
                'driver'     => 'stripe',
                'created_at' => $transaction['created_at'],
                'reference'  => Str::random(12),
            ])
        );

        $this->info("Storing transactions for {$alice->email}...");

        collect([
            ['created_at' => now()],
            ['created_at' => now()->subDays(28)],
            ['created_at' => now()->subDays(28 * 2)],
            ['created_at' => now()->subDays(28 * 3)],
            ['created_at' => now()->subDays(28 * 4)],
        ])->each(
            fn ($transaction) => $alice->transactions()->create([
                'status'     => 'completed',
                'note'       => $businessPackPlan->name,
                'currency'   => $businessPackPlan->currency,
                'amount'     => $businessPackPlan->amount,
                'driver'     => 'paystack',
                'created_at' => $transaction['created_at'],
                'reference'  => Str::random(12),
            ])
        );

        $howdySubscription->driver()->create([
            'driver'                 => 'paypal',
            'driver_subscription_id' => Str::random(),
        ]);

        $aliceSubscription->driver()->create([
            'driver'                 => 'paystack',
            'driver_subscription_id' => Str::random(),
        ]);

        $johanSubscription->driver()->create([
            'driver'                 => 'stripe',
            'driver_subscription_id' => Str::random(),
        ]);
    }

    public function after()
    {
        $this->info('Dispatching jobs...');
        $this->call('queue:work', [
            '--stop-when-empty' => true,
        ]);
    }
}
