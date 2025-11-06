<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PixFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function usuario_consegue_criar_e_simular_pagamento_pix()
    {
        // Simula resposta da API PagBank (mockando o endpoint /orders)
        Http::fake([
            // Qualquer URL que termine em /orders
            'pagseguro.uol.com.br/*/orders' => Http::response([
                'id' => 'fake-order-id',
                'qr_codes' => [[
                    'text' => '00020126360014BR.GOV.BCB.PIX0114+551198765432040000530398654040.005802BR5920NOME TESTE6009SAO PAULO',
                    'links' => [[
                        'href' => 'https://fake.qrcode.pagbank.com/qrcode.png'
                    ]]
                ]],
            ], 200),

            // Caso o env() use outra base (como localhost para testes)
            '*' => Http::response([
                'id' => 'fake-order-id',
                'qr_codes' => [[
                    'text' => 'FAKEPIXCODE123456',
                    'links' => [[
                        'href' => 'https://fake.qrcode.pagbank.com/qrcode.png'
                    ]]
                ]],
            ], 200),
        ]);

        // Cria usuário e autentica via Sanctum
        $user = User::factory()->create(['saldo' => 0]);
        $this->actingAs($user, 'sanctum');

        // Cria o PIX (o Http::fake intercepta a chamada externa)
        $pixResponse = $this->postJson('/api/pix/create', [
            'amount' => 100,
            'description' => 'Depósito de teste',
        ])
            ->assertStatus(200)
            ->json();

        $transactionId = $pixResponse['transaction']['id'] ?? null;
        $this->assertNotNull($transactionId, 'Transação PIX não foi criada.');

        // Simula o pagamento (webhook fake)
        $simulateResponse = $this->postJson("/api/pix/simulate/{$transactionId}")
            ->assertStatus(200)
            ->json();

        // Verifica saldo e status
        $user->refresh();
        $transaction = Transaction::find($transactionId);

        $this->assertEquals(100, $user->saldo);
        $this->assertEquals('completed', $transaction->status);
        $this->assertEquals($user->saldo, $simulateResponse['new_balance']);
    }
}