<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminTransacoesTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_consegue_listar_transacoes()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Sanctum::actingAs($admin, ['*']);

        $response = $this->getJson('/api/admin/transactions/all');

        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function usuario_comum_nao_pode_listar_transacoes()
    {
        $user = User::factory()->create(['role' => 'user']);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/admin/transactions/all');

        $response->assertStatus(403);
    }
}
