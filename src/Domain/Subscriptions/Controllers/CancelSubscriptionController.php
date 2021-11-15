<?php
namespace Domain\Subscriptions\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use VueFileManager\Subscription\Domain\Subscriptions\Resources\SubscriptionResource;

class CancelSubscriptionController extends Controller
{
    public function __invoke(): SubscriptionResource
    {
        $user = Auth::user();

        $user->subscription->cancel();

        return new SubscriptionResource($user->subscription);
    }
}
