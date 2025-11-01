<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Para admin: retorna todas as transações sem paginação
     */
    public function all(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $transactions = Transaction::with(['fromUser', 'toUser'])
            ->orderBy('created_at', 'desc')
            ->get();

        $transactions->transform(function ($transaction) {
            $tipo = ($transaction->fromUser?->id === $transaction->from_user_id) ? 'enviada' : 'recebida';

            return [
                'id' => $transaction->id,
                'tipo' => $tipo,
                'valor' => number_format($transaction->amount, 2, ',', '.'),
                'status' => $transaction->status,
                'data' => $transaction->created_at->format('d/m/Y H:i'),
                'remetente' => $transaction->fromUser?->nome ?? 'Usuário deletado',
                'destinatario' => $transaction->toUser?->nome ?? 'Usuário deletado',
            ];
        });

        return response()->json($transactions);
    }

    /**
     * Para admin: retorna transações paginadas com filtros
     */
    public function indexAdmin(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $query = Transaction::with(['fromUser', 'toUser']);

        // Filtros de data
        if ($request->filled('data_inicial')) {
            $query->whereDate('created_at', '>=', $request->data_inicial);
        }
        if ($request->filled('data_final')) {
            $query->whereDate('created_at', '<=', $request->data_final);
        }

        // Filtros de valor
        if ($request->filled('valor_min')) {
            $query->where('amount', '>=', $request->valor_min);
        }
        if ($request->filled('valor_max')) {
            $query->where('amount', '<=', $request->valor_max);
        }

        // Filtro por tipo (enviada/recebida)
        if ($request->filled('tipo')) {
            if ($request->tipo === 'enviada') {
                $query->whereNotNull('from_user_id');
            } elseif ($request->tipo === 'recebida') {
                $query->whereNotNull('to_user_id');
            }
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10);

        $transactions->getCollection()->transform(function ($transaction) {
            $tipo = ($transaction->fromUser?->id === $transaction->from_user_id) ? 'enviada' : 'recebida';

            return [
                'id' => $transaction->id,
                'tipo' => $tipo,
                'valor' => number_format($transaction->amount, 2, ',', '.'),
                'status' => $transaction->status,
                'data' => $transaction->created_at->format('d/m/Y H:i'),
                'remetente' => $transaction->fromUser?->nome ?? 'Usuário deletado',
                'destinatario' => $transaction->toUser?->nome ?? 'Usuário deletado',
            ];
        });

        return response()->json($transactions);
    }

    /**
     * Cria uma transferência entre usuários autenticados
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'to_user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $fromUser = $request->user();
        $toUserId = (int) $data['to_user_id'];
        $amount = (float) $data['amount'];

        if ($fromUser->id === $toUserId) {
            return response()->json(['message' => 'Não é possível transferir para si mesmo'], 422);
        }

        try {
            $transaction = DB::transaction(function () use ($fromUser, $toUserId, $amount) {
                // Lock para evitar race condition
                $from = DB::table('users')->where('id', $fromUser->id)->lockForUpdate()->first();
                $to = DB::table('users')->where('id', $toUserId)->lockForUpdate()->first();

                if (!$from || !$to) {
                    throw new \Exception('Usuário não encontrado');
                }

                if (bccomp($from->saldo, $amount, 2) === -1) {
                    throw new \Exception('Saldo insuficiente');
                }

                // Atualiza saldos
                DB::table('users')->where('id', $fromUser->id)
                    ->update(['saldo' => DB::raw("saldo - {$amount}")]);

                DB::table('users')->where('id', $toUserId)
                    ->update(['saldo' => DB::raw("saldo + {$amount}")]);

                // Cria registro da transação
                return Transaction::create([
                    'from_user_id' => $fromUser->id,
                    'to_user_id' => $toUserId,
                    'amount' => $amount,
                    'status' => 'completed',
                ]);
            }, 5);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if ($msg === 'Saldo insuficiente') {
                return response()->json(['message' => $msg], 422);
            }

            return response()->json(['message' => 'Erro ao processar transação: ' . $msg], 500);
        }

        return response()->json($transaction, 201);
    }

    /**
     * Histórico do usuário autenticado
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $transactions = Transaction::with(['fromUser', 'toUser'])
            ->where(function ($q) use ($user) {
                $q->where('from_user_id', $user->id)
                  ->orWhere('to_user_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $transactions->getCollection()->transform(function ($transaction) use ($user) {
            $tipo = ($transaction->from_user_id === $user->id) ? 'enviada' : 'recebida';

            return [
                'id' => $transaction->id,
                'tipo' => $tipo,
                'valor' => number_format($transaction->amount, 2, ',', '.'),
                'status' => $transaction->status,
                'data' => $transaction->created_at->format('d/m/Y H:i'),
                'remetente' => $transaction->fromUser?->nome ?? 'Usuário deletado',
                'destinatario' => $transaction->toUser?->nome ?? 'Usuário deletado',
            ];
        });

        return response()->json($transactions);
    }

    public function deposit(Request $request)
    {
    $data = $request->validate([
        'amount' => 'required|numeric|min:0.01',
    ]);

    $user = $request->user();

    DB::table('users')
        ->where('id', $user->id)
        ->update(['saldo' => DB::raw("saldo + {$data['amount']}")]);

    return response()->json(['message' => 'Depósito realizado com sucesso'], 200);
    }
}