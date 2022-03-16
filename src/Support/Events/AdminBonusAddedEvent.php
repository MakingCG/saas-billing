<?php
namespace VueFileManager\Subscription\Support\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class AdminBonusAddedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public $user,
        public string $bonus
    ) {
    }
}
