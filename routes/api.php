<?php

use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\MakananController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Customer\CartController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// login dan register
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// setelah login
Route::group(['middleware' => 'auth:sanctum'], function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Akses Penjual
Route::group(['middleware' => ['auth:sanctum', 'Penjual'], 'prefix' => 'admin'], function () {
    // Kategori
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::post('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Makanan
    Route::get('/makanans', [MakananController::class, 'index']);
    Route::post('/makanans', [MakananController::class, 'store']);
    Route::get('/makanans/{id}', [MakananController::class, 'show']);
    Route::post('/makanans/{id}', [MakananController::class, 'update']);
    Route::delete('/makanans/{id}', [MakananController::class, 'destroy']);
});

Route::group(['middleware' => ['auth:sanctum','Pembeli']], function () {

    // Makanan
    Route::get('/makanans', [MakananController::class, 'index']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::get('/cart/{id}', [CartController::class, 'show']);
    Route::patch('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    // Transaction
    Route::get('/transaction', [TransactionController::class, 'index']);
    Route::post('/transaction', [TransactionController::class, 'store']);
    Route::get('/transaction/{id}', [TransactionController::class, 'show']);
    Route::delete('/transaction/{id}', [TransactionController::class, 'destroy']);
});