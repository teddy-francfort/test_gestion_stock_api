<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Movement;
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
            'price' => fake()->randomFloat(2, 1, 500),
            'quantity' => fake()->numberBetween(0, 100),
            'deleted_at' => null,
            'created_at' => $now,
            'updated_at' => $now->addDay(),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            Movement::query()->create([
                'user_id' => null,
                'product_id' => $product->id,
                'price' => $product->price,
                'quantity' => $product->quantity,
            ]);
        });
    }
}
