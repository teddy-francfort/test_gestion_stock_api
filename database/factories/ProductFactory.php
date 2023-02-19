<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $now = now();

        return [
            'name' => 'Produit test '.fake()->word(),
            'description' => fake()->text(),
            'quantity' => fake()->numberBetween(0, 100),
            'deleted_at' => null,
            'created_at' => $now,
            'updated_at' => $now->addDay(),
        ];
    }
}
