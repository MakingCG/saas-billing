<?php

namespace VueFileManager\Subscription\Support\Engines;

use ErrorException;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\BadRequestError;
use VueFileManager\Subscription\Domain\Plans\DTO\CreateFixedPlanData;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;

class RazorpayEngine implements Engine
{

    /**
     * @inheritDoc
     */
    public function getPlan(string $planId): array
    {
        // TODO: Implement getPlan() method.
    }

    /**
     * @inheritDoc
     */
    public function createFixedPlan(CreateFixedPlanData $data): array
    {
        $api = new Api(
            config('subscription.credentials.razorpay.key'),
            config('subscription.credentials.razorpay.secret')
        );

        $period = [
            'day' => 'daily',
            'week' => 'weekly',
            'month' => 'monthly',
            'year' => 'yearly',
        ];

        try {
            $response = $api->plan->create([
                'period' => $period[$data->interval],
                'interval' => 1,
                'item' => [
                    'name' => $data->name,
                    'description' => $data->description,
                    'amount' => (int)($data->amount * 100), // price needs to be translated to cents
//                    'currency' => $data->currency
                    'currency' => 'INR'
                ],
                'notes' => $data->features
            ]);
        } catch (BadRequestError $exception) {
            throw new ErrorException($exception->getMessage());
        }
        return [
            'id'   => $response['id'],
            'name' => $data->name,
        ];
    }

    /**
     * @inheritDoc
     */
    public function updatePlan(Plan $plan): Response
    {
        // TODO: Implement updatePlan() method.
    }

    /**
     * @inheritDoc
     */
    public function deletePlan(string $planId): void
    {
        // TODO: Implement deletePlan() method.
    }

    /**
     * @inheritDoc
     */
    public function createCustomer(array $user): null|Response
    {
        // TODO: Implement createCustomer() method.
    }

    /**
     * @inheritDoc
     */
    public function updateCustomer(array $user): null|Response
    {
        // TODO: Implement updateCustomer() method.
    }

    /**
     * @inheritDoc
     */
    public function getSubscription(string $subscriptionId): Response
    {
        // TODO: Implement getSubscription() method.
    }

    /**
     * @inheritDoc
     */
    public function createSubscription(Plan $plan, $user = null): array
    {
        // TODO: Implement createSubscription() method.
    }

    /**
     * @inheritDoc
     */
    public function swapSubscription(Subscription $subscription, Plan $plan): Response
    {
        // TODO: Implement swapSubscription() method.
    }

    /**
     * @inheritDoc
     */
    public function updateSubscription(Subscription $subscription, ?Plan $plan = null): array
    {
        // TODO: Implement updateSubscription() method.
    }

    /**
     * @inheritDoc
     */
    public function cancelSubscription(Subscription $subscription): Response
    {
        // TODO: Implement cancelSubscription() method.
    }

    /**
     * @inheritDoc
     */
    public function webhook(Request $request)
    {
        // TODO: Implement webhook() method.
    }
}
