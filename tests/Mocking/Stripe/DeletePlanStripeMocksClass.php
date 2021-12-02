<?php
namespace Tests\Mocking\Stripe;

use Illuminate\Support\Facades\Http;

class DeletePlanStripeMocksClass
{
    public function __invoke($plan)
    {
        return Http::fake([
            'https://api.stripe.com/v1/plans/*' => Http::response([
                'id'      => 'price_1K1wJPB9m4sTKy1qcHaLkXki',
                'object'  => 'plan',
                'deleted' => true,
            ]),
        ]);
    }
}
