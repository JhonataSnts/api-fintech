<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PagBankService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('PAGBANK_URL_SANDBOX', 'https://sandbox.api.pagseguro.com'), '/');
        $this->token = env('PAGBANK_TOKEN_SANDBOX');
    }

    public function createPixOrder(array $payload)
    {
        $url = "{$this->baseUrl}/orders";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        Log::info('PagBank sandbox: criando order PIX', [
            'url' => $url,
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        return $response;
    }
}