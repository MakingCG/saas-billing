<?php
namespace VueFileManager\Subscription\Support\Miscellaneous\Stripe\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use VueFileManager\Subscription\Support\EngineManager;
use VueFileManager\Subscription\Support\Services\StripeHttpService;

class CreateStripeSessionController
{
    public function __construct(
        private StripeHttpService $api,
        private EngineManager $engine,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $user = Auth::user();

        // Create a customer
        $customerId = $user->customerId('stripe') ?? $this->createCustomer($user);

        $session = $this->api->post('/checkout/sessions', [
            'success_url' => url('/platform/files'),
            'cancel_url'  => url('/platform/files'),
            'line_items'  => [
                [
                    'price'    => $request->input('planCode'),
                    'quantity' => 1,
                ],
            ],
            'mode'        => 'subscription',
            'customer'    => $customerId,
        ]);

        // Return stripe checkout url
        return response([
            'url' => $session->json()['url'],
        ], 201);
    }

    private function createCustomer($user)
    {
        $customer = $this->engine
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
