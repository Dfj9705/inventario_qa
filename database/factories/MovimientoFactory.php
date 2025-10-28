<?php

namespace Database\Factories;

use App\Models\Almacen;
use App\Models\Movimiento;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movimiento>
 */
class MovimientoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Movimiento>
     */
    protected $model = Movimiento::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'almacen_id' => Almacen::factory(),
            'tipo' => Movimiento::TIPO_ENTRADA,
            'cantidad' => $this->faker->numberBetween(1, 100),
            'descripcion' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the movement is an entry.
     */
    public function entrada(?int $cantidad = null): static
    {
        return $this->state(function () use ($cantidad) {
            return [
                'tipo' => Movimiento::TIPO_ENTRADA,
                'cantidad' => $cantidad ?? $this->faker->numberBetween(1, 100),
            ];
        });
    }

    /**
     * Indicate that the movement is an exit.
     */
    public function salida(?int $cantidad = null): static
    {
        return $this->state(function () use ($cantidad) {
            return [
                'tipo' => Movimiento::TIPO_SALIDA,
                'cantidad' => $cantidad ?? $this->faker->numberBetween(1, 50),
            ];
        });
    }
}
