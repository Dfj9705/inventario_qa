<?php

namespace App\Http\Controllers;

use App\Models\Existencia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExistenciaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'producto_id' => ['sometimes', 'integer', 'exists:productos,id'],
            'almacen_id' => ['sometimes', 'integer', 'exists:almacens,id'],
        ]);

        $query = Existencia::with(['producto', 'almacen'])->orderBy('producto_id');

        if (isset($validated['producto_id'])) {
            $query->where('producto_id', $validated['producto_id']);
        }

        if (isset($validated['almacen_id'])) {
            $query->where('almacen_id', $validated['almacen_id']);
        }

        return response()->json($query->get());
    }
}
