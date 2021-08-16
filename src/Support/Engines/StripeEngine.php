<?php
namespace Support\Engines;

use Illuminate\Support\Str;
use Cartalyst\Stripe\Stripe;
use Domain\Plans\DTO\CreatePlanData;

class StripeEngine implements Engine
{
    public Stripe $stripe;

    public function __construct()
    {
        $this->stripe = resolve(Stripe::class)
            ->make(config('subscription.credentials.stripe.secret'), '2020-03-02');
    }

    public function hello(): string
    {
        return "Hello, I'm Stripe!";
    }

    public function createPlan(CreatePlanData $data): array
    {
        // Create stripe product
        $product = $this->stripe
            ->products()
            ->create([
                'name'        => $data->name,
                'description' => $data->description,
                'metadata'    => [
                    'amount' => $data->amount,
                ],
            ]);

        // Create stripe plan and attach product into it
        $plan = $this->stripe
            ->plans()
            ->create([
                'id'       => Str::slug($data->name),
                'amount'   => $data->price,
                'interval' => $data->interval,
                'currency' => 'EUR',
                'product'  => $product['id'],
            ]);

        return [
            'id'   => $plan['id'],
            'name' => $product['name'],
        ];
    }
}
