<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'estado' => ['sometimes', 'boolean'],
        ]);

        $query = Producto::with(['existencias.almacen'])
            ->orderByDesc('created_at');

        if ($request->filled('estado')) {
            $query->where('estado', $validated['estado']);
        }

        $perPage = $validated['per_page'] ?? 15;

        $productos = $query->paginate($perPage);

        return response()->json($productos);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:255', 'unique:productos,sku'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'estado' => ['sometimes', 'boolean'],
        ]);

        $producto = Producto::create($validated);

        return response()->json($producto->loadMissing('existencias.almacen'), 201);
    }

    public function show(Producto $producto): JsonResponse
    {
        return response()->json($producto->load(['existencias.almacen']));
    }

    public function update(Request $request, Producto $producto): JsonResponse
    {
        $validated = $request->validate([
            'sku' => ['sometimes', 'string', 'max:255', Rule::unique('productos', 'sku')->ignore($producto->id)],
            'nombre' => ['sometimes', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['sometimes', 'numeric', 'min:0'],
            'estado' => ['sometimes', 'boolean'],
        ]);

        $producto->fill($validated)->save();

        return response()->json($producto->load(['existencias.almacen']));
    }

    public function destroy(Producto $producto): JsonResponse
    {
        $producto->delete();

        return response()->json(null, 204);
    }
}
