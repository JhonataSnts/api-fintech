<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_consegue_fazer_login_com_credenciais_corretas(): void
    {
        $user = User::factory()->create([
            'email' => 'teste@fintech.com',
            'password' => bcrypt('123456'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'teste@fintech.com',
            'password' => '123456',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['user', 'access_token']);
    }

    public function test_usuario_nao_consegue_fazer_login_com_senha_errada(): void
    {
        $user = User::factory()->create([
            'email' => 'teste@fintech.com',
            'password' => bcrypt('123456'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'teste@fintech.com',
            'password' => 'senha_errada',
        ]);

        $response->assertStatus(401);
    }
}