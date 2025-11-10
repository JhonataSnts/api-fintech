<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Exceptions\Transactions\{
    InsufficientFundsException,
    SelfTransferException,
    UserNotFoundException
};

class TransactionService
{
    public function transfer(int $fromUserId, int $toUserId, float $amount): Transaction
    {
        if ($fromUserId === $toUserId) {
            throw new SelfTransferException();
        }

        return DB::transaction(function () use ($fromUserId, $toUserId, $amount) {
            $from = DB::table('users')->where('id', $fromUserId)->lockForUpdate()->first();
            $to = DB::table('users')->where('id', $toUserId)->lockForUpdate()->first();

            if (!$from || !$to) {
                throw new UserNotFoundException();
            }

            if (bccomp($from->saldo, $amount, 2) === -1) {
                throw new InsufficientFundsException();
            }

            DB::table('users')->where('id', $fromUserId)->update(['saldo' => DB::raw("saldo - {$amount}")]);
            DB::table('users')->where('id', $toUserId)->update(['saldo' => DB::raw("saldo + {$amount}")]);

            return Transaction::create([
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'amount' => $amount,
                'status' => 'completed',
            ]);
        }, 3);
    }

    public function deposit($user, float $amount): Transaction
    {
        $user->saldo += $amount;
        $user->save();

        return Transaction::create([
            'from_user_id' => $user->id,
            'to_user_id' => $user->id,
            'amount' => $amount,
            'type' => 'deposit',
            'status' => 'completed',
            'description' => 'Depósito manual',
        ]);
    }
}