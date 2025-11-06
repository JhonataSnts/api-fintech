<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;

class PagBankController extends Controller
{
    /**
     * Cria uma nova ordem no PagBank e salva a transação localmente
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string'
        ]);

        $token = env('PAGBANK_TOKEN'); // no .env
        $sandboxUrl = env('PAGBANK_URL', 'https://sandbox.api.pagseguro.com');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json'
        ])->post("$sandboxUrl/orders", [
            "reference_id" => "transacao_" . uniqid(),
            "customer" => [
                "name"   => "Jonatas Teste",
                "email"  => "jonatas@testesandbox.com",
                "tax_id" => "11144477735"
            ],
            "items" => [[
                "name" => $request->description ?? 'Depósito Pix',
                "quantity" => 1,
                "unit_amount" => intval($request->amount * 100)
            ]],
            "qr_codes" => [[
                "amount" => ["value" => intval($request->amount * 100)]
            ]]
        ]);

        if ($response->failed()) {
            return response()->json(['error' => $response->json()], 400);
        }

        $data = $response->json();

        // salva no banco
        $transaction = Transaction::create([
            'external_id' => $data['id'],
            'amount' => $request->amount,
            'status' => 'pending',
            'description' => $request->description ?? 'Depósito Pix',
            'qr_payload' => $data['qr_codes'][0]['text'] ?? null,
            'qr_image_url' => $data['qr_codes'][0]['links'][0]['href'] ?? null,
        ]);

        return response()->json([
            'transaction' => $transaction,
            'pagbank_order' => $data
        ]);
    }

    /**
     * Webhook para atualizações de pagamento
     */
    public function webhook(Request $request)
    {
        $payload = $request->json()->all();

        if (!empty($payload['reference_id']) && !empty($payload['charges'][0]['status'])) {
            $reference = $payload['reference_id'];
            $status = $payload['charges'][0]['status'];

            $transaction = Transaction::where('external_id', $reference)->first();

            if ($transaction) {
                $transaction->update(['status' => $status]);
            }
        }

        return response()->json(['message' => 'Webhook recebido com sucesso']);
    }
}