<?php


namespace Makingcg\Subscription\Engines;


use Makingcg\Subscription\Services\FlutterWaveHttp;

class FlutterWaveEngine extends Engine
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

    public function createPlan($data): array
    {
        $response = $this->api->post('/payment-plans', $data);

        return $response->json();
    }
}
