<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products_with_filters(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Producto::factory()->count(2)->create(['estado' => true]);
        Producto::factory()->inactive()->create();

        $response = $this->getJson('/api/productos?estado=1&per_page=2');

        $response->assertOk()->assertJson(
            fn(AssertableJson $json) => $json
                ->has('data', 2)
                ->where('per_page', 2)
                ->etc()
        );

        $this->assertTrue(collect($response->json('data'))
            ->every(fn(array $producto) => (int) $producto['estado'] === 1));
    }

    public function test_can_create_product(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $payload = [
            'sku' => 'SKU-1234',
            'nombre' => 'Producto de prueba',
            'descripcion' => 'Descripción del producto',
            'precio' => 150.75,
            'estado' => true,
        ];

        $response = $this->postJson('/api/productos', $payload);

        $response->assertCreated()->assertJsonFragment([
            'sku' => 'SKU-1234',
            'nombre' => 'Producto de prueba',
        ]);

        $this->assertDatabaseHas('productos', [
            'sku' => 'SKU-1234',
            'nombre' => 'Producto de prueba',
        ]);
    }

    public function test_cannot_create_product_with_existing_sku(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Producto::factory()->create(['sku' => 'SKU-1234']);

        $response = $this->postJson('/api/productos', [
            'sku' => 'SKU-1234',
            'nombre' => 'Producto duplicado',
            'precio' => 50,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['sku']);
    }
}
