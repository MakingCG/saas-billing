<?php

namespace VueFileManager\Subscription\App\Console\Commands;

use Illuminate\Console\Command;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Domain\Plans\Actions\StorePlanForPaymentServiceAction;

class GenerateDemoPlansCommand extends Command
{
    public $signature = 'subscription:demo-plans';

    public $description = 'Generate demo plans';

    public function __construct(
        private StorePlanForPaymentServiceAction $storePlanForPaymentService,
    )
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Setting up new plans demo data...');

        // To tasks
        $this->generateFixedPlans();

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
                $data = CreatePlanData::fromArray([
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
