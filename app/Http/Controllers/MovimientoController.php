<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class MovimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $movimientos = Movimiento::query()
            ->with(['producto', 'almacen'])
            ->latest()
            ->get();

        return response()->json($movimientos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'almacen_id' => ['required', 'integer', 'exists:almacenes,id'],
            'tipo' => ['required', 'in:' . Movimiento::TIPO_ENTRADA . ',' . Movimiento::TIPO_SALIDA],
            'cantidad' => ['required', 'integer', 'min:1'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->input('tipo') === Movimiento::TIPO_SALIDA) {
                $stockActual = Movimiento::stockPara(
                    (int) $request->input('producto_id'),
                    (int) $request->input('almacen_id')
                );

                if ($stockActual < (int) $request->input('cantidad')) {
                    $validator->errors()->add('cantidad', 'No hay suficiente stock para realizar el movimiento de salida.');
                }
            }
        });

        $data = $validator->validate();

        $movimiento = Movimiento::create($data);
        $movimiento->load(['producto', 'almacen']);

        return response()->json($movimiento, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Movimiento $movimiento): JsonResponse
    {
        $movimiento->load(['producto', 'almacen']);

        return response()->json($movimiento);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Movimiento $movimiento): Response
    {
        $movimiento->delete();

        return response()->noContent();
    }
}
