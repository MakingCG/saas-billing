<?php
namespace VueFileManager\Subscription\Support\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Promise\PromiseInterface;

class PayStackHttp
{
    private string $bearer;
    private string $api;

    public function __construct()
    {
        $this->bearer = config('subscription.credentials.paystack.secret');
        $this->api = 'https://api.paystack.co';
    }

    public function post($url, $data): PromiseInterface | Response
    {
        return Http::withHeaders([
            'Authorization' => "Bearer $this->bearer",
            'Content-Type'  => 'application/json',
        ])->post("{$this->api}$url", $data);
    }
}
