<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PagSeguroPaymentService
{
    protected $email;
    protected $token;
    protected $baseUrl;

    public function __construct()
    {
        $config = config('pagseguro.sandbox');

        $this->email = $config['email'];
        $this->token = $config['token'];
        $this->baseUrl = $config['base_url'];
    }

    /**
     * Cria um pagamento (exemplo: depósito)
     */
    public function createDeposit(User $user, float $amount)
    {
        $payload = [
            'reference_id' => 'DEPOSIT-' . uniqid(),
            'description' => 'Depósito na conta digital',
            'amount' => [
                'value' => intval($amount * 100), // em centavos
                'currency' => 'BRL'
            ],
            'payment_method' => [
                'type' => 'CREDIT_CARD',
                // em produção: os dados do cartão viriam do front (tokenizados)
            ],
            'notification_urls' => [
                url('/api/webhook/payment')
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->baseUrl}/orders", $payload);

            if ($response->failed()) {
                Log::error('Erro PagSeguro', ['response' => $response->body()]);
                return ['error' => 'Falha ao criar transação no PagSeguro'];
            }

            $data = $response->json();

            // Aqui você poderia salvar localmente a transação como "pendente"
            Transaction::create([
                'sender_id' => null,
                'receiver_id' => $user->id,
                'amount' => $amount,
                'type' => 'deposit',
                'status' => 'pending',
                'description' => 'Depósito iniciado via PagSeguro',
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::error('Exceção PagSeguro', ['message' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }
}
