<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PagSeguroPaymentService
{
    protected string $token;
    protected string $baseUrl;

    public function __construct()
{
    $this->token = env('PAGSEGURO_TOKEN', 'fake-token'); // ou config('pagseguro.sandbox.app_key')
    $this->baseUrl = rtrim(config('pagseguro.sandbox.base_url', 'https://sandbox.api.pagseguro.com'), '/');
}

    /**
     * Cria um pagamento PIX e retorna o QR Code/link
     */
    public function createDeposit(User $user, float $amount)
    {
        try {
            $payload = [
                'reference_id' => 'DEPOSIT-' . uniqid(),
                'description' => 'Depósito via PIX',
                'amount' => [
                    'value' => intval($amount * 100), // em centavos
                    'currency' => 'BRL',
                ],
                'notification_urls' => [
                    config('app.url') . '/api/webhook/payment'
                ],
                'customer' => [
                    'name' => $user->name ?? 'Cliente Teste',
                    'email' => $user->email ?? 'teste@example.com',
                    'tax_id' => '00000000191', // CPF genérico para sandbox
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/pix/payments', $payload);

            $pixData = $response->json();
            Log::info('🔹 Resposta PagSeguro PIX:', $pixData);

            if (!$response->successful() || empty($pixData['qr_codes'][0]['links'][0]['href'])) {
                Log::error('❌ Falha ao gerar PIX:', [
                    'status' => $response->status(),
                    'body' => $pixData
                ]);
                return ['error' => 'Falha ao gerar QR Code PIX'];
            }

            // Retorna os dados principais
            return [
                'qrcode_link' => $pixData['qr_codes'][0]['links'][0]['href'],
                'qrcode_text' => $pixData['qr_codes'][0]['text'],
                'payment_id' => $pixData['id'],
            ];

        } catch (\Throwable $e) {
            Log::error('💥 Erro interno PagSeguroService', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            return ['error' => 'Erro interno ao processar depósito'];
        }
    }

    /**
     * Atualiza o status de uma transação (via webhook)
     */
    public function updateTransactionStatus(string $referenceId, string $status)
    {
        $transaction = Transaction::where('id', $referenceId)->first();
        if (!$transaction) return null;

        $transaction->status = match ($status) {
            'PAID', 'SETTLED', 'COMPLETED' => 'completed',
            'CANCELLED', 'REFUNDED' => 'failed',
            default => 'pending',
        };
        $transaction->save();

        if ($transaction->status === 'completed') {
            $user = $transaction->toUser;
            if ($user) {
                $user->saldo += $transaction->amount;
                $user->save();
            }
        }

        return $transaction;
    }
}