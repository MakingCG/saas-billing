<?php
namespace VueFileManager\Subscription\App\Console\Commands;

use Illuminate\Console\Command;

class SetupDemoDataCommand extends Command
{
    public $signature = 'subscription:demo';

    public $description = 'Generate invoicing demo data';

    public function handle()
    {
        $this->info('Setting up subscription demo data');

        $this->create_demo_content();

        $this->info('Dispatching jobs...');
        $this->call('queue:work', [
            '--stop-when-empty' => true,
        ]);

        $this->info('Everything is done, congratulations! 🥳🥳🥳');
    }

    public function create_demo_content()
    {
        dd('hello');
    }
}
