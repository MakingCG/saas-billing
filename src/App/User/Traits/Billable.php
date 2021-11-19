<?php
namespace VueFileManager\Subscription\App\User\Traits;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use VueFileManager\Subscription\Domain\Customers\Models\Customer;
use VueFileManager\Subscription\Domain\Transactions\Models\Transaction;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

trait Billable
{
    public function hasSubscription(): bool
    {
        return $this->subscription && ($this->subscription->active() || $this->subscription->onGracePeriod());
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'user_id', 'id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
