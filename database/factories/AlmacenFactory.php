<?php

namespace Database\Factories;

use App\Models\Almacen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Almacen>
 */
class AlmacenFactory extends Factory
{
    protected $model = Almacen::class;

    public function definition(): array
    {
        return [
            'nombre' => 'Almacén ' . $this->faker->unique()->word(),
            'ubicacion' => $this->faker->city(),
        ];
    }
}
