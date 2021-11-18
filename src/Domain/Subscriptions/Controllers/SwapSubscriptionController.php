<?php
namespace VueFileManager\Subscription\Domain\Subscriptions\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Auth;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;

class SwapSubscriptionController extends Controller
{
    public function __invoke(Plan $plan): Response
    {
        $user = Auth::user();

        // Swap existing user subscription
        return $user->subscription->swap($plan);
    }
}
