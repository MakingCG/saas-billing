<?php
namespace VueFileManager\Subscription\Domain\Subscriptions\Controllers;

use Illuminate\Routing\Controller;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class EditSubscriptionController extends Controller
{
    public function __invoke(Subscription $subscription)
    {
        return response($subscription->generateUpdateLink(), 201);
    }
}
