<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PixTransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PagBankController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Authenticated User
    Route::get('/users/me', fn (Request $request) => response()->json([
        'id' => $request->user()->id,
        'name' => $request->user()->nome,
        'email' => $request->user()->email,
        'balance' => $request->user()->saldo,
    ]));

    // Users CRUD
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // User Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);  // User's transaction history
    Route::post('/transactions', [TransactionController::class, 'store']); // Create a new transaction

    // Deposit
    Route::post('/deposit', [TransactionController::class, 'deposit']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['isAdmin'])->prefix('admin')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'indexAdmin']);
        Route::get('/transactions/all', [TransactionController::class, 'all']);
    });

    /*
    |--------------------------------------------------------------------------
    | PIX Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('pix')->group(function () {
        Route::post('/create', [PixTransactionController::class, 'create']);
        Route::post('/simulate/{id}', [PixTransactionController::class, 'simulate']);
    });
});

/*
|--------------------------------------------------------------------------
| Webhook (Public)
|--------------------------------------------------------------------------
*/
Route::post('/pix/webhook', [PixTransactionController::class, 'webhook']);
