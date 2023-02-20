<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->route = 'products.destroy';
});

test('guest cannot delete a product', function () {
    $product = Product::factory()->create();
    $this->assertDatabaseCount(Product::class, 1);

    $response = $this->deleteJson(route($this->route, ['product' => $product]));

    $product->refresh();
    $response->assertUnauthorized();
    $this->assertDatabaseCount(Product::class, 1);
    $this->assertFalse($product->trashed());
});

test('authenticated user can delete an active product', function () {
    $user = User::factory()->create();
    /** @var Product $product */
    $product = Product::factory()->create();
    $this->assertDatabaseCount(Product::class, 1);

    $response = $this
        ->actingAs($user)
        ->deleteJson(route($this->route, ['product' => $product]));

    $product->refresh();
    $response->assertNoContent();
    $this->assertDatabaseCount(Product::class, 1);
    $this->assertTrue($product->trashed());
});

test('authenticated user cannot delete a soft deleted product', function () {
    $user = User::factory()->create();
    /** @var Product $product */
    $product = Product::factory()->trashed()->create();
    $this->assertDatabaseCount(Product::class, 1);

    $response = $this
        ->actingAs($user)
        ->deleteJson(route($this->route, ['product' => $product]));

    $product->refresh();
    $response->assertForbidden();
    $this->assertDatabaseCount(Product::class, 1);
});
