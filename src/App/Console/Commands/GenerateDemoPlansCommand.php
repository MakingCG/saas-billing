<?php
namespace VueFileManager\Subscription\App\Console\Commands;

use Illuminate\Console\Command;
use VueFileManager\Subscription\Domain\Plans\DTO\CreateFixedPlanData;
use VueFileManager\Subscription\Domain\Plans\DTO\CreateMeteredPlanData;
use VueFileManager\Subscription\Domain\Plans\Actions\StoreFixedPlanAction;
use VueFileManager\Subscription\Domain\Plans\Actions\StoreMeteredPlanAction;

class GenerateDemoPlansCommand extends Command
{
    public $signature = 'subscription:demo-plans {type=fixed}';

    public $description = 'Generate demo plans';

    public function __construct(
        private StoreFixedPlanAction $storeFixedPlan,
        private StoreMeteredPlanAction $storeMeteredPlan,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->argument('type') === 'metered') {
            $this->info('Setting up new metered plans demo data...');

            $this->generateMeteredPlans();
        }

        if ($this->argument('type') === 'fixed') {
            $this->info('Setting up new fixed plans demo data...');

            $this->generateFixedPlans();
        }

        $this->after();

        $this->info('Everything is done, congratulations! 🥳🥳🥳');
    }

    public function generateFixedPlans()
    {
        // Define plans
        $plans = [
            [
                'type'        => 'fixed',
                'name'        => 'Professional Pack',
                'description' => 'Best for all professionals',
                'currency'    => 'USD',
                'features'    => [
                    'max_storage_amount' => 200,
                    'max_team_members'   => 20,
                ],
                'intervals'   => [
                    [
                        'interval' => 'month',
                        'amount'   => 9.99,
                    ],
                    [
                        'interval' => 'year',
                        'amount'   => 99.49,
                    ],
                ],
            ],
            [
                'type'        => 'fixed',
                'name'        => 'Business Pack',
                'description' => 'Best for business needs',
                'currency'    => 'USD',
                'features'    => [
                    'max_storage_amount' => 500,
                    'max_team_members'   => 50,
                ],
                'intervals'   => [
                    [
                        'interval' => 'month',
                        'amount'   => 29.99,
                    ],
                    [
                        'interval' => 'year',
                        'amount'   => 189.99,
                    ],
                ],
            ],
            [
                'type'        => 'fixed',
                'name'        => 'Elite Pack',
                'description' => 'Best for all your needs',
                'currency'    => 'USD',
                'features'    => [
                    'max_storage_amount' => 2000,
                    'max_team_members'   => -1,
                ],
                'intervals'   => [
                    [
                        'interval' => 'month',
                        'amount'   => 59.99,
                    ],
                    [
                        'interval' => 'year',
                        'amount'   => 349.99,
                    ],
                ],
            ],
        ];

        // Create plans
        foreach ($plans as $plan) {
            foreach ($plan['intervals'] as $interval) {
                $data = CreateFixedPlanData::fromArray([
                    'type'        => $plan['type'],
                    'name'        => $plan['name'],
                    'description' => $plan['description'],
                    'features'    => $plan['features'],
                    'currency'    => $plan['currency'],
                    'amount'      => $interval['amount'],
                    'interval'    => $interval['interval'],
                ]);

                $this->info("Creating plan with name: {$plan['name']} and interval: {$interval['interval']}");

                // Store plans to the database and gateway
                ($this->storeFixedPlan)($data);
            }
        }
    }

    public function generateMeteredPlans()
    {
        // Define plans
        $plans = [
            [
                'type'        => 'metered',
                'name'        => 'Pay as You Go',
                'description' => 'Best for all professionals',
                'currency'    => 'USD',
                'meters'      => [
                    [
                        'key'                => 'bandwidth',
                        'aggregate_strategy' => 'sum_of_usage',
                        'tiers'              => [
                            [
                                'first_unit' => 1,
                                'last_unit'  => null,
                                'per_unit'   => 0.019,
                                'flat_fee'   => null,
                            ],
                        ],
                    ],
                    [
                        'key'                => 'storage',
                        'aggregate_strategy' => 'maximum_usage',
                        'tiers'              => [
                            [
                                'first_unit' => 1,
                                'last_unit'  => null,
                                'per_unit'   => 0.09,
                                'flat_fee'   => 2.49,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // Create plans
        foreach ($plans as $plan) {
            $data = CreateMeteredPlanData::fromArray([
                'type'        => $plan['type'],
                'name'        => $plan['name'],
                'meters'      => $plan['meters'],
                'currency'    => $plan['currency'],
                'description' => $plan['description'],
            ]);

            $this->info("Creating plan with name: {$plan['name']}");

            // Store plans to the database and gateway
            ($this->storeMeteredPlan)($data);
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
