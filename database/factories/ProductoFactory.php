<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Producto>
     */
    protected $model = Producto::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->words(2, true),
            'sku' => strtoupper($this->faker->bothify('SKU-#####')),
            'descripcion' => $this->faker->optional()->sentence(),
        ];
    }
}
