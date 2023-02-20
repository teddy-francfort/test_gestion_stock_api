<?php

declare(strict_types=1);

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;

dataset('products', [
    'active products' => [fn () => Product::factory()->count(3)->create()],
    'soft deleted products' => [fn () => Product::factory()->trashed()->count(3)->create()],
]);

beforeEach(function () {
    $this->route = 'products.index';
});

test('guest cannot access products', function () {
    $response = $this->getJson(route($this->route));
    $response->assertUnauthorized();
})->with('products');

test('authenticated user can access products (active and soft deleted)', function ($products) {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->getJson(route($this->route));

    $response->assertSuccessful();
    $productResource = ProductResource::collection($products)->toJson();
    $response->assertJson(json_decode($productResource, true));
    $response->assertJsonMissing(['movements']);
})->with('products');
