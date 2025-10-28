<?php

namespace Tests\Feature;

use App\Models\Almacen;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MovimientoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_create_outgoing_movement_without_stock(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $producto = Producto::factory()->create();
        $almacen = Almacen::factory()->create();

        $response = $this->postJson('/api/movimientos', [
            'producto_id' => $producto->id,
            'almacen_id' => $almacen->id,
            'tipo' => 'OUT',
            'cantidad' => 10,
        ]);

        $response->assertStatus(422)->assertJson([
            'message' => 'La cantidad solicitada supera el stock disponible.',
        ]);

        $this->assertDatabaseEmpty('movimientos');
    }

    public function test_creating_incoming_movement_increases_stock(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $producto = Producto::factory()->create();
        $almacen = Almacen::factory()->create();

        $response = $this->postJson('/api/movimientos', [
            'producto_id' => $producto->id,
            'almacen_id' => $almacen->id,
            'tipo' => 'IN',
            'cantidad' => 5,
            'motivo' => 'Reposición de stock',
        ]);

        $response->assertCreated()->assertJsonFragment([
            'producto_id' => $producto->id,
            'almacen_id' => $almacen->id,
            'tipo' => 'IN',
            'cantidad' => 5,
        ]);

        $this->assertDatabaseHas('existencias', [
            'producto_id' => $producto->id,
            'almacen_id' => $almacen->id,
            'stock' => 5,
        ]);

        $this->assertDatabaseHas('movimientos', [
            'producto_id' => $producto->id,
            'almacen_id' => $almacen->id,
            'tipo' => 'IN',
            'cantidad' => 5,
        ]);
    }
}
