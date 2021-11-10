<?php
namespace VueFileManager\Subscription\App\Console\Commands;

use Illuminate\Console\Command;
use VueFileManager\Subscription\Support\EngineManager;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Domain\Plans\Actions\StorePlanAndCreateDriverVersionAction;

class SetupDemoDataCommand extends Command
{
    public $signature = 'subscription:demo';

    public $description = 'Generate subscription demo data';

    public function __construct(
        private EngineManager $subscription,
        private StorePlanAndCreateDriverVersionAction $storePlanAndCreateDriverVersion,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Setting up subscription demo data');

        $this->create_plans();

        $this->info('Dispatching jobs...');
        $this->call('queue:work', [
            '--stop-when-empty' => true,
        ]);

        $this->info('Everything is done, congratulations! 🥳🥳🥳');
    }

    public function create_plans()
    {
        // Define plans
        $plans = [
            [
                'name'        => 'Professional Pack',
                'description' => 'Best for all professionals',
                'features'    => [
                    'max_storage_amount' => 200,
                    'max_team_members'   => 20,
                ],
                'intervals' => [
                    [
                        'interval'    => 'month',
                        'amount'      => 10,
                    ],
                    [
                        'interval'    => 'year',
                        'amount'      => 99,
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
                    'amount'      => $interval['amount'],
                    'interval'    => $interval['interval'],
                ]);

                $this->info("Creating plan with name: {$plan['name']} and interval: {$interval['interval']}");

                // Store plans to the database and gateway
                ($this->storePlanAndCreateDriverVersion)($data);
            }
        }
    }
}
