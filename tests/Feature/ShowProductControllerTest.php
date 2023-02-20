<?php

declare(strict_types=1);

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->route = 'products.show';
});

test('guest cannot access a product', function () {
    $product = Product::factory()->create();
    $response = $this->getJson(route($this->route, ['product' => $product]));
    $response->assertUnauthorized();
});

test('authenticated user can access an active product', function () {
    /** @var Product $product */
    $product = Product::factory()->create();
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->getJson(route($this->route, ['product' => $product]));

    $response->assertSuccessful();
    $productResource = ProductResource::make($product->loadMissing('movements'))->toJson();
    $response->assertJson(json_decode($productResource, true));
    $response->assertJsonStructure(['movements' => [['id', 'price', 'quantity', 'created_at', 'updated_at']]]);
});

test('authenticated user can access an soft deleted product', function () {
    /** @var Product $product */
    $product = Product::factory()->trashed()->create();
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->getJson(route($this->route, ['product' => $product]));

    $response->assertSuccessful();
    $productResource = ProductResource::make($product->loadMissing('movements'))->toJson();
    $response->assertJson(json_decode($productResource, true));
    $response->assertJsonStructure(['movements' => [['id', 'price', 'quantity', 'created_at', 'updated_at']]]);
});
