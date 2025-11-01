<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Processa um depósito (fake ou real)
     */
    public function processDeposit(User $user, float $amount): Transaction
    {
        return DB::transaction(function () use ($user, $amount) {
            $user->balance += $amount;
            $user->save();

            return Transaction::create([
                'sender_id' => null,
                'receiver_id' => $user->id,
                'amount' => $amount,
                'type' => 'deposit',
                'status' => 'completed',
                'description' => 'Depósito via PaymentService (simulado)',
            ]);
        });
    }

    /**
     * Processa uma transferência entre usuários
     */
    public function createTransaction(User $sender, User $receiver, float $amount): Transaction
    {
        return DB::transaction(function () use ($sender, $receiver, $amount) {
            if ($sender->balance < $amount) {
                throw new \Exception('Saldo insuficiente');
            }

            $sender->balance -= $amount;
            $sender->save();

            $receiver->balance += $amount;
            $receiver->save();

            return Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'type' => 'transfer',
                'status' => 'completed',
                'description' => 'Transferência entre usuários',
            ]);
        });
    }

    /**
     * Simula callback de pagamento (webhook)
     */
    public function simulateWebhook(array $payload): Transaction
    {
        // Exemplo de payload:
        // [ 'user_id' => 1, 'amount' => 100.00 ]
        $user = User::findOrFail($payload['user_id']);
        return $this->processDeposit($user, $payload['amount']);
    }
}