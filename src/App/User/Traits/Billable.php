<?php
namespace App\User\Traits;

use Illuminate\Database\Eloquent\Relations\HasOne;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

trait Billable
{
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }
}
