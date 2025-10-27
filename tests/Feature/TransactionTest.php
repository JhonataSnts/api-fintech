<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function usuario_consegue_fazer_transferencia_valida()
    {
        $fromUser = User::factory()->create(['saldo' => 100]);
        $toUser = User::factory()->create(['saldo' => 50]);

        $this->actingAs($fromUser, 'sanctum');

        $response = $this->postJson('/api/transactions', [
            'to_user_id' => $toUser->id,
            'amount' => 25,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('transactions', [
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
            'amount' => 25,
            'status' => 'completed',
        ]);

        $this->assertEquals(75, $fromUser->fresh()->saldo);
        $this->assertEquals(75, $toUser->fresh()->saldo);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function nao_pode_transferir_para_si_mesmo()
    {
        $user = User::factory()->create(['saldo' => 100]);
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/transactions', [
            'to_user_id' => $user->id,
            'amount' => 10,
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Não é possível transferir para si mesmo']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function nao_pode_transferir_com_saldo_insuficiente()
    {
        $fromUser = User::factory()->create(['saldo' => 10]);
        $toUser = User::factory()->create(['saldo' => 0]);

        $this->actingAs($fromUser, 'sanctum');

        $response = $this->postJson('/api/transactions', [
            'to_user_id' => $toUser->id,
            'amount' => 50,
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Saldo insuficiente']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function usuario_autenticado_ve_seu_historico_de_transacoes()
    {
        $user = User::factory()->create();
        $outro = User::factory()->create();

        Transaction::factory()->create([
            'from_user_id' => $user->id,
            'to_user_id' => $outro->id,
            'amount' => 30,
            'status' => 'completed',
        ]);

        Transaction::factory()->create([
            'from_user_id' => $outro->id,
            'to_user_id' => $user->id,
            'amount' => 15,
            'status' => 'completed',
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/transactions');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => [['id', 'tipo', 'valor', 'status', 'data', 'remetente', 'destinatario']]]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function apenas_admin_pode_listar_todas_as_transacoes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        Transaction::factory()->count(3)->create();

        $this->actingAs($admin, 'sanctum');
        $response = $this->getJson('/api/admin/transactions/all');
        $response->assertStatus(200);

        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/admin/transactions/all');
        $response->assertStatus(403);
    }
}