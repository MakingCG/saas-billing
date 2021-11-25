<?php
namespace VueFileManager\Subscription\App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Domain\Plans\Actions\StorePlanForPaymentServiceAction;

class SetupDemoDataCommand extends Command
{
    public $signature = 'subscription:demo';

    public $description = 'Generate subscription demo data';

    public function __construct(
        private StorePlanForPaymentServiceAction $storePlanForPaymentService,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Setting up subscription demo data');

        // To tasks
        //$this->create_plans();
        $this->create_demo_subscription();

        $this->after();

        $this->info('Everything is done, congratulations! 🥳🥳🥳');
    }

    public function create_demo_subscription()
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

        $howdySubscription = $howdy->subscription()->create([
            'plan_id'    => $professionalPackPlan->id,
            'name'       => $professionalPackPlan->name,
            'status'     => 'active',
            'created_at' => now()->subDays(14),
            'updated_at' => now()->subDays(14),
        ]);

        $aliceSubscription = $alice->subscription()->create([
            'plan_id'    => $businessPackPlan->id,
            'name'       => $businessPackPlan->name,
            'status'     => 'active',
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(9),
        ]);

        $johanSubscription = $johan->subscription()->create([
            'plan_id'    => $professionalPackPlan->id,
            'name'       => $professionalPackPlan->name,
            'status'     => 'cancelled',
            'ends_at'    => now()->addDays(18),
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        collect([
            ['created_at' => now()->subDays(2)],
            ['created_at' => now()->subDays(26)],
            ['created_at' => now()->subDays(26 * 2)],
            ['created_at' => now()->subDays(26 * 3)],
            ['created_at' => now()->subDays(26 * 4)],
            ['created_at' => now()->subDays(26 * 5)],
        ])->each(
            fn ($transaction) =>
            $howdy->transactions()->create([
                'status'     => 'completed',
                'plan_name'  => $professionalPackPlan->name,
                'currency'   => $professionalPackPlan->currency,
                'amount'     => $professionalPackPlan->amount,
                'driver'     => 'paypal',
                'created_at' => $transaction['created_at'],
                'reference'  => Str::random(12),
            ])
        );

        collect([
            ['created_at' => now()->subDay()],
            ['created_at' => now()->subDays(29)],
            ['created_at' => now()->subDays(29 * 2)],
            ['created_at' => now()->subDays(29 * 3)],
        ])->each(
            fn ($transaction) =>
            $johan->transactions()->create([
                'status'     => 'completed',
                'plan_name'  => $professionalPackPlan->name,
                'currency'   => $professionalPackPlan->currency,
                'amount'     => $professionalPackPlan->amount,
                'driver'     => 'paypal',
                'created_at' => $transaction['created_at'],
                'reference'  => Str::random(12),
            ])
        );

        collect([
            ['created_at' => now()],
            ['created_at' => now()->subDays(28)],
            ['created_at' => now()->subDays(28 * 2)],
            ['created_at' => now()->subDays(28 * 3)],
            ['created_at' => now()->subDays(28 * 4)],
        ])->each(
            fn ($transaction) =>
            $alice->transactions()->create([
                'status'     => 'completed',
                'plan_name'  => $businessPackPlan->name,
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
            'driver'                 => 'paypal',
            'driver_subscription_id' => Str::random(),
        ]);
    }

    public function create_plans()
    {
        // Define plans
        $plans = [
            [
                'name'        => 'Professional Pack',
                'description' => 'Best for all professionals',
                'currency'    => 'USD',
                'features'    => [
                    'max_storage_amount' => 200,
                    'max_team_members'   => 20,
                ],
                'intervals' => [
                    [
                        'interval'    => 'month',
                        'amount'      => 9.99,
                    ],
                    [
                        'interval'    => 'year',
                        'amount'      => 99.49,
                    ],
                ],
            ],
            [
                'name'        => 'Business Pack',
                'description' => 'Best for business needs',
                'currency'    => 'USD',
                'features'    => [
                    'max_storage_amount' => 500,
                    'max_team_members'   => 50,
                ],
                'intervals' => [
                    [
                        'interval'    => 'month',
                        'amount'      => 29.99,
                    ],
                    [
                        'interval'    => 'year',
                        'amount'      => 189.99,
                    ],
                ],
            ],
            [
                'name'        => 'Elite Pack',
                'description' => 'Best for all your needs',
                'currency'    => 'USD',
                'features'    => [
                    'max_storage_amount' => 2000,
                    'max_team_members'   => -1,
                ],
                'intervals' => [
                    [
                        'interval'    => 'month',
                        'amount'      => 59.99,
                    ],
                    [
                        'interval'    => 'year',
                        'amount'      => 349.99,
                    ],
                ],
            ],
        ];

        // Create plans
        foreach ($plans as $plan) {
            foreach ($plan['intervals'] as $interval) {
                $data = CreatePlanData::fromArray([
                    'name'        => $plan['name'],
                    'description' => $plan['description'],
                    'features'    => $plan['features'],
                    'currency'    => $plan['currency'],
                    'amount'      => $interval['amount'],
                    'interval'    => $interval['interval'],
                ]);

                $this->info("Creating plan with name: {$plan['name']} and interval: {$interval['interval']}");

                // Store plans to the database and gateway
                ($this->storePlanForPaymentService)($data);
            }
        }
    }

    public function after()
    {
        $this->info('Dispatching jobs...');
        $this->call('queue:work', [
            '--stop-when-empty' => true,
        ]);
    }
}
