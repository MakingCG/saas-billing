<?php
namespace VueFileManager\Subscription\Domain\Subscriptions\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use VueFileManager\Subscription\Domain\Subscriptions\Resources\SubscriptionResource;

class CancelSubscriptionController extends Controller
{
    public function __invoke(): SubscriptionResource
    {
        $user = Auth::user();

        // Cancel existing user subscription
        $user->subscription->cancel();

        return new SubscriptionResource($user->subscription);
    }
}
