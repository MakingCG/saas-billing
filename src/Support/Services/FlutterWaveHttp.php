<?php
namespace Support\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Promise\PromiseInterface;

class FlutterWaveHttp
{
    private string $bearer;
    private string $api;

    public function __construct()
    {
        $this->bearer = config('subscription.credentials.flutter-wave.secret');
        $this->api = 'https://api.flutterwave.com/v3';
    }

    public function post($url, $data): PromiseInterface | Response
    {
        return Http::withHeaders([
            'Authorization' => "Bearer $this->bearer",
        ])->post("{$this->api}$url", $data);
    }
}
