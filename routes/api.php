<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PixTransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/users/me', fn (Request $request) => response()->json([
        'id' => $request->user()->id,
        'name' => $request->user()->nome,
        'email' => $request->user()->email,
        'balance' => $request->user()->saldo,
    ]));

    
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    
    Route::get('/transactions', [TransactionController::class, 'index']);  
    Route::post('/transactions', [TransactionController::class, 'store']); 

    
    Route::post('/deposit', [TransactionController::class, 'deposit']);

    
    Route::middleware(['isAdmin'])->prefix('admin')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'indexAdmin']);
        Route::get('/transactions/all', [TransactionController::class, 'all']);
    });

    
    Route::prefix('pix')->group(function () {
        Route::post('/create', [PixTransactionController::class, 'create']);
        Route::post('/simulate/{id}', [PixTransactionController::class, 'simulate']);
    });
});


Route::post('/pix/webhook', [PixTransactionController::class, 'webhook']);
