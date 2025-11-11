<?php

namespace App\Http\Controllers;

use App\Services\PagBankService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;

class PixTransactionController extends Controller
{
    /**
     * Cria uma transação PIX via PagBank (sandbox)
     */
    public function create(Request $request, PagBankService $pagbank)
{
    $request->validate([
        'amount' => 'required|numeric|min:1',
        'description' => 'nullable|string|max:255',
    ]);

    $user = $request->user();

    $payload = [
        "reference_id" => "deposito_" . uniqid(),
        "customer" => [
            "name" => $user->name ?? "Cliente Sandbox",
            "email" => $user->email ?? "cliente@sandbox.com",
            "tax_id" => "11144477735"
        ],
        "items" => [
            [
                "name" => $request->description ?? "Depósito Sandbox",
                "quantity" => 1,
                "unit_amount" => intval($request->amount * 100)
            ]
        ],
        "qr_codes" => [
            [
                "amount" => ["value" => intval($request->amount * 100)]
            ]
        ]
    ];

    $response = $pagbank->createPixOrder($payload);

    if ($response->failed()) {
        return response()->json([
            'error' => true,
            'message' => 'Erro ao criar PIX sandbox',
            'details' => $response->json()
        ], 400);
    }

    $data = $response->json();

    $transaction = Transaction::create([
        'from_user_id' => null,
        'to_user_id'   => $user->id,
        'amount'       => $request->amount,
        'status'       => 'pending',
        'description'  => $request->description ?? 'Depósito PIX',
        'qr_payload'   => $data['qr_codes'][0]['text'] ?? null,
        'qr_image_url' => $data['qr_codes'][0]['links'][0]['href'] ?? null,
        'external_id'  => $data['id'] ?? null,
    ]);

    return response()->json([
        'success' => true,
        'transaction' => $transaction,
        'pagbank' => $data,
    ]);
}

    
    public function webhook(Request $request)
    {
        $payload = $request->json()->all();

        if (!empty($payload['reference_id']) && !empty($payload['charges'][0]['status'])) {
            $transaction = Transaction::where('external_id', $payload['reference_id'])->first();
            if ($transaction) {
                $transaction->update(['status' => $payload['charges'][0]['status']]);
            }
        }

        return response()->json(['message' => 'Webhook recebido com sucesso']);
    }

    public function simulate($id)
{
    $transaction = Transaction::findOrFail($id);

    if ($transaction->status === 'completed') {
        return response()->json([
            'message' => 'Essa transação já foi completada.'
        ], 400);
    }

    // Atualiza o status
    $transaction->update(['status' => 'completed']);

    // Atualiza o saldo do usuário
    $user = $transaction->toUser;
    $user->saldo += $transaction->amount;
    $user->save();

    // ✅ Retorna saldo correto e evita duplicação de save()
    return response()->json([
        'success' => true,
        'message' => 'Pagamento simulado com sucesso',
        'transaction' => $transaction,
        'new_balance' => $user->saldo
    ], 200);
}
}