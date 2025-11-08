<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\DepositRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Exceptions\Transactions\{
    InsufficientFundsException,
    SelfTransferException,
    UserNotFoundException
};

class TransactionController extends Controller
{
    use AuthorizesRequests;
    
    protected TransactionService $service;

    public function __construct(TransactionService $service)
    {
        $this->service = $service;
    }

    public function all(Request $request): JsonResponse
{
    $this->authorize('viewAny', Transaction::class);

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

    try {
        $transaction = $this->service->transfer($fromUser->id, $toUserId, $amount);
        return response()->json(new TransactionResource($transaction), 201);
    } catch (SelfTransferException | InsufficientFundsException $e) {
        return response()->json(['message' => $e->getMessage()], 422);
    } catch (UserNotFoundException $e) {
        return response()->json(['message' => $e->getMessage()], 404);
    } catch (\Throwable $e) {
        return response()->json(['message' => 'Erro interno no servidor'], 500);
    }
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

    return response()->json([
        'data' => TransactionResource::collection($transactions)
    ], 200);
}


}