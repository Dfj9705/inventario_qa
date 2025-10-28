<?php

namespace Database\Factories;

use App\Models\Almacen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Almacen>
 */
class AlmacenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Almacen>
     */
    protected $model = Almacen::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Almacén ' . $this->faker->unique()->citySuffix(),
            'ubicacion' => $this->faker->optional()->city(),
        ];
    }
}
