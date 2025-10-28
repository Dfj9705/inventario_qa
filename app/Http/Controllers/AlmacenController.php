<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AlmacenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(Almacen::query()->orderBy('nombre')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
        ]);

        $almacen = Almacen::create($data);

        return response()->json($almacen, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Almacen $almacen): JsonResponse
    {
        return response()->json($almacen);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Almacen $almacen): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
        ]);

        $almacen->update($data);

        return response()->json($almacen);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Almacen $almacen): Response
    {
        $almacen->delete();

        return response()->noContent();
    }
}
