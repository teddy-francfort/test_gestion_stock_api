<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        ProductFactory::new()
            ->count(10)
            ->state(new Sequence(
                ['deleted_at' => null],
                ['deleted_at' => $now]
            ))
            ->sequence(fn (Sequence $sequence) => ['name' => 'Produit '.($sequence->index + 1)])
            ->create();
    }
}
