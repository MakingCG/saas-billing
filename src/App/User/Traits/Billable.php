<?php
namespace VueFileManager\Subscription\App\User\Traits;

use Illuminate\Database\Eloquent\Relations\HasOne;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

trait Billable
{
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'user_id', 'id');
    }
}
