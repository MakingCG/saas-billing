<?php
namespace Tests\Mocking\Stripe;

use Illuminate\Support\Facades\Http;

class GetPlanStripeMocksClass
{
    public function __invoke()
    {
        return Http::fake([
            'https://api.stripe.com/v1/products/*' => Http::response([
                'id'                   => 'prod_KkKCKsfkSYPhcj',
                'object'               => 'product',
                'active'               => true,
                'attributes'           => [],
                'created'              => 1639067568,
                'description'          => 'Best for all your needs',
                'images'               => [],
                'livemode'             => false,
                'metadata'             => [],
                'name'                 => 'Elite Pack',
                'package_dimensions'   => null,
                'shippable'            => null,
                'statement_descriptor' => null,
                'tax_code'             => null,
                'type'                 => 'service',
                'unit_label'           => null,
                'updated'              => 1639067568,
                'url'                  => 'http://localhost',
            ]),
            'https://api.stripe.com/v1/prices?product=*' => Http::response([
                'object'   => 'list',
                'data'     => [
                    [
                        'id'                  => 'price_1K4pY4B9m4sTKy1qdtaZQjhM',
                        'object'              => 'price',
                        'active'              => true,
                        'billing_scheme'      => 'per_unit',
                        'created'             => 1639067568,
                        'currency'            => 'usd',
                        'livemode'            => false,
                        'lookup_key'          => null,
                        'metadata'            => [],
                        'nickname'            => null,
                        'product'             => 'prod_KkKCKsfkSYPhcj',
                        'recurring'           => [
                            'aggregate_usage'   => null,
                            'interval'          => 'month',
                            'interval_count'    => 1,
                            'trial_period_days' => null,
                            'usage_type'        => 'licensed',
                        ],
                        'tax_behavior'        => 'unspecified',
                        'tiers_mode'          => null,
                        'transform_quantity'  => null,
                        'type'                => 'recurring',
                        'unit_amount'         => 5999,
                        'unit_amount_decimal' => '5999',
                    ],
                ],
                'has_more' => false,
                'url'      => '/v1/prices',
            ]),
        ]);
    }
}
