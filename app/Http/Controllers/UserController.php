<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Lista todos os usuários (opcional: apenas admins)
     */
    public function index()
    {
        $authUser = auth()->user();

        // Exemplo: permitir apenas se for admin
        // if (!$authUser->is_admin) {
        //     return response()->json(['message' => 'Acesso negado'], 403);
        // }

        return response()->json(User::all());
    }

    /**
     * Cria um novo usuário (registrar via API ou admin)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'saldo' => 'nullable|numeric'
        ]);

        $user = User::create($data);

        return response()->json($user, 201);
    }

    /**
     * Mostra os dados de um usuário específico
     */
    public function show(User $usuario)
    {
        $authUser = auth()->user();

        // Permitir apenas se for o próprio usuário
        if ($authUser->id !== $usuario->id) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        return response()->json($usuario);
    }

    /**
     * Atualiza os dados do usuário
     */
    public function update(Request $request, User $usuario)
    {
        $authUser = auth()->user();

        // Permitir apenas se for o próprio usuário
        if ($authUser->id !== $usuario->id) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $data = $request->validate([
            'nome' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $usuario->id,
            'saldo' => 'sometimes|numeric'
        ]);

        $usuario->update($data);

        return response()->json($usuario);
    }

    /**
     * Deleta o usuário
     */
    public function destroy(User $usuario)
    {
        $authUser = auth()->user();

        // Permitir apenas se for o próprio usuário
        if ($authUser->id !== $usuario->id) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $usuario->delete();

        return response()->json(['message' => 'Usuário excluído com sucesso']);
    }
}