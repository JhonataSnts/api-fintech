<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Simula um depósito
     */
    public function deposit(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);

        $user = Auth::user();
        $transaction = $this->paymentService->processDeposit($user, $request->amount);

        return response()->json([
            'message' => 'Depósito realizado com sucesso!',
            'transaction' => $transaction,
            'new_balance' => $user->balance
        ]);
    }

    /**
     * Simula webhook (para testes locais)
     */
    public function webhook(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $transaction = $this->paymentService->simulateWebhook($request->all());

        return response()->json([
            'message' => 'Webhook recebido e processado',
            'transaction' => $transaction,
        ]);
    }
}