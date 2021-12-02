<?php
namespace Tests\Mocking\Stripe;

use Illuminate\Support\Facades\Http;

class UpdateCustomerStripeMocksClass
{
    public function __invoke($user)
    {
        return Http::fake([
            'https://api.stripe.com/v1/customers/*' => Http::response([
                'id'               => 'cus_KhKzgeRbNMvLSX',
                'object'           => 'customer',
                'address'          => null,
                'balance'          => 0,
                'created'          => 1638378579,
                'currency'         => 'eur',
                'default_source'   => null,
                'delinquent'       => false,
                'description'      => null,
                'discount'         => null,
                'email'            => $user->email,
                'invoice_prefix'   => '702D8E8',
                'invoice_settings' => [
                    'custom_fields'          => null,
                    'default_payment_method' => null,
                    'footer'                 => null,
                ],
                'livemode' => false,
                'metadata' => [
                    'order_id' => '6735',
                ],
                'name'              => null,
                'phone'             => null,
                'preferred_locales' => [
                ],
                'shipping'   => null,
                'tax_exempt' => 'none',
            ]),
        ]);
    }
}