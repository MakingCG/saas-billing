<?php
namespace Support\Engines;

use Domain\Plans\DTO\CreatePlanData;
use Support\Services\FlutterWaveHttp;

class FlutterWaveEngine implements Engine
{
    public FlutterWaveHttp $api;

    public function __construct()
    {
        $this->api = resolve(FlutterWaveHttp::class);
    }

    public function hello(): string
    {
        return "Hello, I'm FlutterWave!";
    }

    public function createPlan(CreatePlanData $data): array
    {
        $response = $this->api->post('/payment-plans', [
            'amount'   => $data->price,
            'name'     => $data->name,
            'interval' => $this->mapInterval($data->interval),
        ]);

        return $response->json();
    }

    private function mapInterval(string $interval): string
    {
        return match ($interval) {
            'day'     => 'daily',
            'week'    => 'weekly',
            'month'   => 'monthly',
            'quarter' => 'quarterly',
            'year'    => 'yearly',
        };
    }
}
