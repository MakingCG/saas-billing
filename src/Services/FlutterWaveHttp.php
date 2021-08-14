<?php


namespace Makingcg\Subscription\Services;


use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class FlutterWaveHttp
{
    private string $bearer;
    private string $api;

    public function __construct()
    {
        $this->bearer = config('vuefilemanager-subscription.credentials.flutter-wave.secret');
        $this->api = 'https://api.flutterwave.com/v3';
    }

    public function post($url, $data): PromiseInterface|Response
    {
        return Http::withHeaders([
            'Authorization' => "Bearer $this->bearer",
        ])->post("{$this->api}$url", $data);
    }
}
