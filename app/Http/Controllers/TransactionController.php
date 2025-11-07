<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\DepositRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    protected TransactionService $service;

    public function __construct(TransactionService $service)
    {
        $this->service = $service;
    }

    public function all(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            abort(403, 'Acesso negado');
        }

        $transactions = Transaction::with(['fromUser', 'toUser'])
            ->latest()
            ->get();

        return response()->json(TransactionResource::collection($transactions));
    }

    public function store(StoreTransactionRequest $request): JsonResponse
{
    $fromUser = $request->user();
    $toUserId = (int) $request->validated()['to_user_id'];
    $amount = (float) $request->validated()['amount'];

    // Evita transferir para si mesmo
    if ($fromUser->id === $toUserId) {
        return response()->json(['message' => 'Não é possível transferir para si mesmo'], 422);
    }

    try {
        $transaction = $this->service->transfer($fromUser->id, $toUserId, $amount);
        return response()->json(new TransactionResource($transaction), 201);
    } catch (\Exception $e) {
        // Corrige o ponto final e o código de status
        $message = trim($e->getMessage(), '.');
        $status = $message === 'Saldo insuficiente' ? 422 : 500;

        return response()->json(['message' => $message], $status);
    }
}

    public function deposit(DepositRequest $request): JsonResponse
    {
        $transaction = $this->service->deposit(Auth::user(), (float) $request->validated()['amount']);
        return response()->json(new TransactionResource($transaction), 200);
    }

    public function index(Request $request): JsonResponse
{
    $user = $request->user();

    $transactions = Transaction::with(['fromUser', 'toUser'])
        ->where(fn($q) => $q
            ->where('from_user_id', $user->id)
            ->orWhere('to_user_id', $user->id))
        ->latest()
        ->get();

    // Garante a estrutura { "data": [...] }
    return response()->json([
        'data' => TransactionResource::collection($transactions)
    ], 200);
}

}