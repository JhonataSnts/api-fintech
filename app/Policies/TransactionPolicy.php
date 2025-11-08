<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TransactionPolicy
{
    /**
     * Determina se o usuário pode visualizar qualquer transação.
     */
    public function viewAny(User $user): bool
    {
        // Apenas admins podem listar todas as transações
        return $user->role === 'admin';
    }

    /**
     * Determina se o usuário pode visualizar uma transação específica.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        // Pode ver se for admin ou estiver envolvido (remetente/destinatário)
        return $user->role === 'admin' ||
               $user->id === $transaction->from_user_id ||
               $user->id === $transaction->to_user_id;
    }

    /**
     * Determina se o usuário pode criar uma transação.
     */
    public function create(User $user): bool
    {
        // Qualquer usuário autenticado pode criar uma transação
        return true;
    }
}
