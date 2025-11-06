<?php
namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FakePaymentService
{
    /**
     * Cria depósito simulado e retorna dados para o front (qrcode_link e texto)
     */
    public function createDeposit(User $user, float $amount)
    {
        // texto PIX simulado (pode ser qualquer string)
        $pixText = "PIX|REF:" . strtoupper(Str::random(8)) . "|USER:" . $user->id . "|AMT:" . number_format($amount, 2, '.', '');

        // Gera um link de QR usando Google Chart API (rápido e sem dependências).
        // (obs: para produção use gerador QR do provedor real)
        $qrSize = 300;
        $qrcode_link = "https://chart.googleapis.com/chart?cht=qr&chs={$qrSize}x{$qrSize}&chl=" . urlencode($pixText);

        // Cria transação pendente no banco
        $transaction = Transaction::create([
            'from_user_id' => null,
            'to_user_id'   => $user->id,
            'amount'       => $amount,
            'status'       => 'pending',
            'description'  => 'Depósito simulado (PIX)',
        ]);

        Log::info('FakePaymentService: depósito simulado criado', [
            'transaction_id' => $transaction->id,
            'amount' => $amount,
            'qrcode_link' => $qrcode_link,
            'pix_text' => $pixText,
        ]);

        return [
            'transaction_id' => $transaction->id,
            'amount' => number_format($amount, 2, ',', '.'),
            'status' => $transaction->status,
            'pix' => [
                'qrcode_text' => $pixText,
                'qrcode_link' => $qrcode_link,
            ],
        ];
    }

    /**
     * Simula callback/webhook de pagamento: marca como completed e atualiza saldo
     */
    public function simulateWebhook(string $transactionId)
    {
        $transaction = Transaction::find($transactionId);
        if (!$transaction) {
            return null;
        }

        $transaction->status = 'completed';
        $transaction->save();

        $user = $transaction->toUser;
        if ($user) {
            $user->saldo += $transaction->amount;
            $user->save();
        }

        Log::info('FakePaymentService: webhook simulado processado', [
            'transaction_id' => $transactionId,
            'new_status' => $transaction->status,
        ]);

        return $transaction;
    }
}