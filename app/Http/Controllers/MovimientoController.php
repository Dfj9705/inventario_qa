<?php

namespace App\Http\Controllers;

use App\Models\Existencia;
use App\Models\Movimiento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MovimientoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'producto_id' => ['sometimes', 'integer', 'exists:productos,id'],
            'almacen_id' => ['sometimes', 'integer', 'exists:almacens,id'],
            'tipo' => ['sometimes', Rule::in(['IN', 'OUT'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Movimiento::with(['producto', 'almacen', 'user'])->orderByDesc('fecha');

        if (isset($validated['producto_id'])) {
            $query->where('producto_id', $validated['producto_id']);
        }

        if (isset($validated['almacen_id'])) {
            $query->where('almacen_id', $validated['almacen_id']);
        }

        if (isset($validated['tipo'])) {
            $query->where('tipo', $validated['tipo']);
        }

        $perPage = $validated['per_page'] ?? 15;

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'almacen_id' => ['required', 'integer', 'exists:almacens,id'],
            'tipo' => ['required', Rule::in(['IN', 'OUT'])],
            'cantidad' => ['required', 'integer', 'min:1'],
            'motivo' => ['nullable', 'string', 'max:255'],
            'fecha' => ['nullable', 'date'],
        ]);

        $existencia = Existencia::firstOrNew([
            'producto_id' => $validated['producto_id'],
            'almacen_id' => $validated['almacen_id'],
        ], [
            'stock' => 0,
        ]);

        if ($validated['tipo'] === 'OUT' && $existencia->stock < $validated['cantidad']) {
            return response()->json([
                'message' => 'La cantidad solicitada supera el stock disponible.',
            ], 422);
        }

        $movimiento = DB::transaction(function () use ($validated, $existencia, $request) {
            $payload = [
                'producto_id' => $validated['producto_id'],
                'almacen_id' => $validated['almacen_id'],
                'tipo' => $validated['tipo'],
                'cantidad' => $validated['cantidad'],
                'motivo' => $validated['motivo'] ?? null,
                'fecha' => $validated['fecha'] ?? now(),
                'user_id' => $request->user()->id,
            ];

            $movimiento = Movimiento::create($payload);

            $delta = $validated['tipo'] === 'IN' ? $validated['cantidad'] : -$validated['cantidad'];
            $existencia->stock += $delta;
            $existencia->save();

            return $movimiento;
        });

        return response()->json($movimiento->load(['producto', 'almacen', 'user']), 201);
    }

    public function show(Movimiento $movimiento): JsonResponse
    {
        return response()->json($movimiento->load(['producto', 'almacen', 'user']));
    }
}
