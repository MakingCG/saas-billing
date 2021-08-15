<?php


namespace Makingcg\Subscription\Engines;


use Cartalyst\Stripe\Stripe;
use Domain\Plans\DTO\CreatePlanData;
use Illuminate\Support\Str;

class StripeEngine implements Engine
{
    public Stripe $stripe;

    public function __construct()
    {
        $this->stripe = resolve(Stripe::class)
            ->make(config('vuefilemanager-subscription.credentials.stripe.secret'), '2020-03-02');
    }

    public function hello(): string
    {
        return "Hello, I'm Stripe!";
    }

    public function createPlan(CreatePlanData $data): array
    {
        // Create product
        $product = $this->stripe
            ->products()
            ->create([
                'name'        => $data->name,
                'description' => $data->description,
                'metadata'    => [
                    'storage' => $data->storage,
                ],
            ]);

        // Create & return plan
        return $this->stripe
            ->plans()
            ->create([
                'id'       => Str::slug($data->name),
                'amount'   => $data->price,
                'interval' => $data->interval,
                'currency' => 'EUR',
                'product'  => $product['id'],
            ]);
    }
}
