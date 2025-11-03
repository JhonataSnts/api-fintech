<?php

namespace App\Services;

use App\Models\User;

class FakePaymentService
{
    public function createDeposit(User $user, float $amount)
    {
        $user->saldo += $amount; // atualiza saldo
        $user->save();

        // Simula a criação de um depósito vinculado ao usuário
        return [
            'status' => 'success',
            'transaction_id' => 'FAKE-' . uniqid(),
            'user_id' => $user->id,
            'amount' => $amount,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    public function getTransactionStatus(string $transactionId)
    {
        // Simulação de verificação de status
        return [
            'transaction_id' => $transactionId,
            'status' => 'approved',
            'updated_at' => now()->toDateTimeString(),
        ];
    }
}