<?php
namespace VueFileManager\Subscription\Domain\Subscriptions\Controllers;

use VueFileManager\Subscription\Domain\Subscriptions\Resources\SubscriptionResource;

class GetUserSubscriptionController
{
    public function __invoke($id)
    {
        $subscription = config('auth.providers.users.model')::find($id)
            ->subscription;

        if ($subscription) {
            return new SubscriptionResource($subscription);
        }

        return response('User do not have subscription', 404);
    }
}
