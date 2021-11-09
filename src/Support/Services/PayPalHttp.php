<?php
namespace VueFileManager\Subscription\Support\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Promise\PromiseInterface;

class PayPalHttp
{
    private string $id;
    private string $secret;

    private string $api;

    public function __construct()
    {
        $this->secret = config('subscription.credentials.paypal.secret');
        $this->id = config('subscription.credentials.paypal.id');

        $this->api = 'https://api-m.sandbox.paypal.com/v1';
    }

    public function get($url): PromiseInterface|Response
    {
        return Http::withToken($this->getAccessToken())
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])->get("{$this->api}$url");
    }

    public function post($url, $data): PromiseInterface|Response
    {
        return Http::withToken($this->getAccessToken())
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->api}$url", $data);
    }

    private function getAccessToken(): string
    {
        return Cache::remember('paypal_access_token', 3600, function () {
            $response = Http::withBasicAuth($this->id, $this->secret)
                ->withBody(
                    'grant_type=client_credentials',
                    'text/plain'
                )
                ->withHeaders([
                    'Content-Type'    => 'application/x-www-form-urlencoded',
                    'Accept'          => 'application/json',
                    'Accept-Language' => 'en_US',
                ])->post("$this->api/oauth2/token");

            return $response->json()['access_token'];
        });
    }
}
