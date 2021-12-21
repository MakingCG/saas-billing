<?php
namespace VueFileManager\Subscription\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GenerateDemoContentCommand extends Command
{
    public $signature = 'subscription:demo {type=fixed}';

    public $description = 'Generate demo content';

    public function handle()
    {
        $this->call('subscription:demo-plans', ['type' => $this->argument('type')]);
        $this->call('subscription:demo-subscriptions', ['type' => $this->argument('type')]);

        // Set default subscription type into VueFileManager settings table
        if (Schema::hasTable('settings')) {
            DB::table('settings')->insert([
                'name'  => 'subscription_type', // TODO: add to setup wizard
                'value' => $this->argument('type'),
            ]);
        }
    }
}
