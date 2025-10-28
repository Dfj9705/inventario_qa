<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\ExistenciaController;
use App\Http\Controllers\MovimientoController;

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
Route::get('/', function () {
    return response()->json(['message' => 'API DE GESTION DE INVENTARIOS']);
});
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::apiResource('productos', ProductoController::class);
    Route::apiResource('almacenes', AlmacenController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('stock', [ExistenciaController::class, 'index']);
    Route::apiResource('movimientos', MovimientoController::class)->only(['index', 'store', 'show']);
});
