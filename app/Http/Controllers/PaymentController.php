<?php

namespace App\Http\Controllers;

use App\Services\PagSeguroPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct()
{
    $driver = env('PAYMENT_DRIVER', 'pagseguro');

    if ($driver === 'fake') {
        $this->paymentService = new \App\Services\FakePaymentService();
    } else {
        $this->paymentService = app(\App\Services\PagSeguroPaymentService::class);
    }
}

    /**
     * Cria um depósito real (via PagSeguro sandbox)
     */
    public function deposit(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);

        $user = Auth::user();
        $transaction = $this->paymentService->createDeposit($user, $request->amount);

        return response()->json([
            'message' => 'Depósito iniciado com sucesso!',
            'transaction' => $transaction,
        ]);
    }

    /**
     * Webhook (simulado por enquanto)
     */
    public function webhook(Request $request)
    {
        return response()->json(['message' => 'Webhook ainda não implementado']);
    }
}