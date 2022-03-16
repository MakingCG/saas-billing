<?php
namespace VueFileManager\Subscription\Support\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class InsufficientBalanceEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public $user
    ) {
    }
}
