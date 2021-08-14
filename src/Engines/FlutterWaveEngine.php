<?php


namespace Makingcg\Subscription\Engines;


use Illuminate\Support\Facades\Http;

class FlutterWaveEngine extends Engine
{

    public function hello(): string
    {
        return "Hello, I'm FlutterWave!";
    }

    public function createPlan($data): array
    {
        $bearer = config('vuefilemanager-subscription.credentials.flutter-wave.secret');

        $response = Http::withHeaders([
            'Authorization' => "Bearer $bearer",
        ])->post('https://api.flutterwave.com/v3/payment-plans', $data);

        return $response->json();
    }
}
