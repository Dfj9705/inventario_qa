<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    public function index(): JsonResponse
    {
        $almacenes = Almacen::with(['existencias.producto'])->orderBy('nombre')->get();

        return response()->json($almacenes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
        ]);

        $almacen = Almacen::create($validated);

        return response()->json($almacen, 201);
    }

    public function update(Request $request, Almacen $almacen): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
        ]);

        $almacen->fill($validated)->save();

        return response()->json($almacen);
    }

    public function destroy(Almacen $almacen): JsonResponse
    {
        $almacen->delete();

        return response()->json(null, 204);
    }
}
