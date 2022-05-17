<?php
namespace VueFileManager\Subscription\Domain\Subscriptions\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;
use VueFileManager\Subscription\Domain\Subscriptions\Resources\SubscriptionCollection;

class GetAllSubscriptionsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $subscriptions = Subscription::sortable(['created_at' => 'desc'])
            ->paginate(20);

        return response()->json(new SubscriptionCollection($subscriptions));
    }
}
