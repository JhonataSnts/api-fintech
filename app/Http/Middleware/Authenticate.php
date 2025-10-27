<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        // Se for uma rota de API, retorna JSON 401 em vez de redirecionar
        if ($request->expectsJson() || $request->is('api/*')) {
            abort(response()->json(['message' => 'Não autenticado.'], 401));
        }

        // Evita erro de rota inexistente em APIs
        return null;
    }
}