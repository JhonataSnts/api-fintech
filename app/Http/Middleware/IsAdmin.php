<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return $next($request);
    }
}