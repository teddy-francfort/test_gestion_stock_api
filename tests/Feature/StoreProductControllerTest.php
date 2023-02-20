<?php

declare(strict_types=1);

use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use Database\Factories\UserFactory;

$testingContext = new class()
{
    public const ROUTE = 'products.store';

    /**
     * @return array<string, string|int>
     */
    public static function getGoodFormData(): array
    {
        return [
            'name' => 'Produit test',
            'description' => 'description',
            'price' => 15.99,
            'quantity' => 10,
        ];
    }
};

test('guest cannot create a product', function () use ($testingContext) {
    $response = $this->postJson(route($testingContext::ROUTE), $testingContext::getGoodFormData());
    $response->assertUnauthorized();
    $this->assertDatabaseCount(Product::class, 0);
});

test('an authenticate user can create a product', function (array $goodData) use ($testingContext) {
    $this->freezeTime();

    /** @var User $user */
    $user = UserFactory::new()->create();
    $formData = array_merge($testingContext::getGoodFormData(), $goodData);

    $response = $this->actingAs($user)->postJson(route($testingContext::ROUTE), $formData);

    $response->assertCreated();
    $this->assertDatabaseCount(Product::class, 1);
    /** @var Product $product */
    $product = Product::query()->first();

    $expectedInDatabase = $formData;
    $expectedInDatabase['price'] = $formData['price'] * 100;
    $this->assertDatabaseHas(Product::class, $expectedInDatabase);
    $this->assertDatabaseHas(Movement::class, [
        'user_id' => $user->getKey(),
        'product_id' => $product->id,
        'quantity' => $product->quantity,
        'price' => $product->price * 100,
        'created_at' => now(),
    ]);
})->with(function () {
    foreach ([0, 12.99, '12.99', 15] as $value) {
        yield "price with value $value" => [['price' => $value]];
    }
    foreach ([0, 15] as $value) {
        yield "quantity with value $value" => [['quantity' => $value]];
    }
});

test('a product cannot be created with wrong data', function (array $wrongData) use ($testingContext) {
    /** @var User $user */
    $user = UserFactory::new()->create();
    $formData = array_merge($testingContext::getGoodFormData(), $wrongData);

    $response = $this->actingAs($user)->postJson(route($testingContext::ROUTE), $formData);

    $response->assertJsonValidationErrors(array_keys($wrongData));

    $this->assertDatabaseCount(Product::class, 0);
})->with(function () {
    foreach ([null, 123] as $value) {
        yield "name with value $value" => [['name' => $value]];
    }
    foreach ([null, 123] as $value) {
        yield "description with value $value" => [['description' => $value]];
    }
    foreach ([null, -5, 'abc', 12.995] as $value) {
        yield "price with value $value" => [['price' => $value]];
    }
    foreach ([null, -5, 'abc'] as $value) {
        yield "quantity with value $value" => [['quantity' => $value]];
    }
});
