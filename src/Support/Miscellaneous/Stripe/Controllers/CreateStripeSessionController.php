<?php
namespace VueFileManager\Subscription\Support\Miscellaneous\Stripe\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use VueFileManager\Subscription\Support\EngineManager;
use VueFileManager\Subscription\Support\Services\StripeHttpClient;

class CreateStripeSessionController
{
    use StripeHttpClient;

    public function __invoke(Request $request): Response
    {
        $user = Auth::user();

        // Get or create a customer
        $customerId = $user->customerId('stripe') ?? $this->createCustomer($user);

        $session = $this->post('/checkout/sessions', [
            'success_url' => url('/platform/files'),
            'cancel_url'  => url('/platform/files'),
            'line_items'  => $this->getPlanPrices(),
            'mode'        => 'subscription',
            'customer'    => $customerId,
        ]);

        // Return stripe checkout url
        return response([
            'url' => $session->json()['url'],
        ], 201);
    }

    private function getPlanPrices(): array
    {
        $plan = resolve(EngineManager::class)
            ->driver('stripe')
            ->getPlan(request()->input('planCode'));

        return collect($plan['prices']['data'])
            ->map(fn ($price) => [
                'price'    => $price['id'],
            ])->toArray();
    }

    private function createCustomer($user)
    {
        $customer = resolve(EngineManager::class)
            ->driver('stripe')
            ->createCustomer([
                'id'      => $user->id,
                'email'   => $user->email,
                'name'    => $user->settings->name ?? null,
                'surname' => $user->settings->name ?? null,
            ]);

        return $customer->json()['id'];
    }
}
