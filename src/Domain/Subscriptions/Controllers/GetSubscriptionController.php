<?php
namespace VueFileManager\Subscription\Domain\Subscriptions\Controllers;

use Illuminate\Support\Facades\Auth;
use VueFileManager\Subscription\Domain\Subscriptions\Resources\SubscriptionResource;

class GetSubscriptionController
{
    public function __invoke()
    {
        $subscription = Auth::user()
            ->subscription;

        if ($subscription) {
            return new SubscriptionResource($subscription);
        }

        return response('User do not have subscription', 404);
    }
}
