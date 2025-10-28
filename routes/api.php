<?php

use App\\Http\\Controllers\\AlmacenController;
use App\\Http\\Controllers\\MovimientoController;
use App\\Http\\Controllers\\ProductoController;
use App\\Http\\Controllers\\StockController;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('productos', ProductoController::class);
Route::apiResource('almacenes', AlmacenController::class);
Route::apiResource('movimientos', MovimientoController::class)->only(['index', 'store', 'show', 'destroy']);
Route::get('stock', [StockController::class, 'index']);
