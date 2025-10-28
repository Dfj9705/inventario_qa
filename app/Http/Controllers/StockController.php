<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use Illuminate\Http\JsonResponse;

class StockController extends Controller
{
    /**
     * Display the stock aggregated by product and warehouse.
     */
    public function index(): JsonResponse
    {
        $stock = Movimiento::stockAgrupado();

        return response()->json($stock);
    }
}
