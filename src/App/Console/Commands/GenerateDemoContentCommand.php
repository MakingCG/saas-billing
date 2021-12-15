<?php
namespace VueFileManager\Subscription\App\Console\Commands;

use Illuminate\Console\Command;

class GenerateDemoContentCommand extends Command
{
    public $signature = 'subscription:demo {type=fixed}';

    public $description = 'Generate demo content';

    public function handle()
    {
        $this->call('subscription:demo-plans', ['type' => $this->argument('type')]);
        $this->call('subscription:demo-subscriptions', ['type' => $this->argument('type')]);
    }
}
