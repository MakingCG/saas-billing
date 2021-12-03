<?php
namespace VueFileManager\Subscription\App\Console\Commands;

use Illuminate\Console\Command;

class GenerateDemoContentCommand extends Command
{
    public $signature = 'subscription:demo';

    public $description = 'Generate demo content';

    public function handle()
    {
        $this->call('subscription:demo-plans');
        $this->call('subscription:demo-subscriptions');
    }
}
