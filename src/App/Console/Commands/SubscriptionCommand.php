<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SubscriptionCommand extends Command
{
    public $signature = 'subscription';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
