<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductUnitController;


Route::get('/test', [ProductUnitController::class, 'test']);

Route::post('/login', [ProductUnitController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('product-units', ProductUnitController::class)->parameters(['product-units' => 'productUnit']);

    Route::post('/logout-all', [AuthController::class, 'logoutAllDevices']);
    Route::get('/sessions', [AuthController::class, 'listSessions']);
});
