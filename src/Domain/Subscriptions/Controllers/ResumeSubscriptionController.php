<?php
namespace Domain\Subscriptions\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use VueFileManager\Subscription\Domain\Subscriptions\Resources\SubscriptionResource;

class ResumeSubscriptionController extends Controller
{
    public function __invoke(): SubscriptionResource
    {
        $user = Auth::user();

        // Resume existing user subscription
        $user->subscription->resume();

        return new SubscriptionResource($user->subscription);
    }
}
