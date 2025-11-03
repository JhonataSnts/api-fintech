<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;

// Rotas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//  Rotas protegidas por token (Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Usuário autenticado
    Route::get('/usuarios/me', fn (Request $request) => response()->json([
    'id' => $request->user()->id,
    'nome' => $request->user()->nome,
    'email' => $request->user()->email,
    'saldo' => $request->user()->saldo,
    ]));

    Route::get('/usuarios', [UserController::class, 'index']);

    Route::post('/transactions/deposit', [PaymentController::class, 'deposit']);

    // CRUD do próprio usuário
    Route::get('/usuarios/{usuario}', [UserController::class, 'show']);
    Route::put('/usuarios/{usuario}', [UserController::class, 'update']);
    Route::delete('/usuarios/{usuario}', [UserController::class, 'destroy']);

    // Transações do usuário autenticado
    Route::post('/transactions', [TransactionController::class, 'store']); // Criar transferência
    Route::get('/transactions', [TransactionController::class, 'index']);  // Histórico pessoal

    //  Rotas ADMIN — protegidas com middleware 'isAdmin'
    Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {
    Route::get('/admin/transactions/all', [TransactionController::class, 'all']);
    Route::get('/admin/transactions', [TransactionController::class, 'indexAdmin']);
    });
});

Route::post('/webhook/payment', [PaymentController::class, 'webhook']); // sem auth, simula callback externa