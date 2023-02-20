<?php

declare(strict_types=1);

use App\Events\ProductQuantityLow;
use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ProductQuantityLowNotification;
use Database\Factories\ProductFactory;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

$testingContext = new class()
{
    public const ROUTE = 'products.update';

    /**
     * @return array<string, string|int>
     */
    public static function getGoodFormData(): array
    {
        return [
            'name' => 'Produit updated',
            'description' => 'description updated',
            'price' => 15.99,
            'quantity' => 10,
        ];
    }
};

test('guest cannot update a product', function () use ($testingContext) {
    /** @var Product $product */
    $product = ProductFactory::new()->create();
    $productBefore = $product->replicate();

    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 1);

    $response = $this->patchJson(route($testingContext::ROUTE, ['product' => $product]), $testingContext::getGoodFormData());
    $response->assertUnauthorized();
    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 1);
    $product->refresh();
    $this->assertEquals($productBefore->getAttributes(), $product->replicate()->getAttributes());
});

test('an authenticate user can update a product', function (array $goodData) use ($testingContext) {
    $this->freezeTime();
    /** @var Product $product */
    $product = ProductFactory::new()->create(['quantity' => 30]);
    /** @var Product $productBefore */
    $productBefore = $product->replicate();
    /** @var User $user */
    $user = UserFactory::new()->create();
    $formData = array_merge($testingContext::getGoodFormData(), $goodData);

    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 1);

    $response = $this
        ->actingAs($user)
        ->patchJson(route($testingContext::ROUTE, ['product' => $product]), $formData);

    $product->refresh();
    $response->assertSuccessful();
    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 2);

    $expectedInDatabase = $formData;
    $expectedInDatabase['id'] = $product->id;
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

test('an authenticate user can update a soft deleted product', function () use ($testingContext) {
    $this->freezeTime();
    /** @var Product $product */
    $product = Product::factory()->trashed()->create(['quantity' => 30]);
    /** @var User $user */
    $user = UserFactory::new()->create();
    $formData = $testingContext::getGoodFormData();

    $this->assertTrue($product->trashed());
    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 1);

    $response = $this
        ->actingAs($user)
        ->patchJson(route($testingContext::ROUTE, ['product' => $product]), $formData);

    $product->refresh();
    $response->assertSuccessful();
    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 2);

    $expectedInDatabase = $formData;
    $expectedInDatabase['id'] = $product->id;
    $expectedInDatabase['price'] = $formData['price'] * 100;
    $this->assertDatabaseHas(Product::class, $expectedInDatabase);
    $this->assertDatabaseHas(Movement::class, [
        'user_id' => $user->getKey(),
        'product_id' => $product->id,
        'quantity' => $product->quantity,
        'price' => $product->price * 100,
        'created_at' => now(),
    ]);
});

test('an event is dispatched when product quantity is low', function () use ($testingContext) {
    Event::fake([ProductQuantityLow::class]);
    Config::set('stock.alert_min_quantity', 5);
    $this->freezeTime();
    /** @var Product $product */
    $product = Product::factory()->trashed()->create(['quantity' => 30]);
    /** @var User $user */
    $user = UserFactory::new()->create();
    $formData = $testingContext::getGoodFormData();

    $this->assertTrue($product->trashed());
    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 1);

    $formData['quantity'] = 20;
    $response = $this
        ->actingAs($user)
        ->patchJson(route($testingContext::ROUTE, ['product' => $product]), $formData)
        ->assertSuccessful();

    $product->refresh();
    $this->assertTrue($product->quantity > Config::get('stock.alert_min_quantity'));
    Event::assertNotDispatched(ProductQuantityLow::class);
    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 2);

    $formData['quantity'] = 4;
    $response = $this
        ->actingAs($user)
        ->patchJson(route($testingContext::ROUTE, ['product' => $product]), $formData)
        ->assertSuccessful();

    $product->refresh();
    $this->assertTrue($product->quantity < Config::get('stock.alert_min_quantity'));
    Event::assertDispatchedTimes(ProductQuantityLow::class, 1);
    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 3);
});

test('an alert is sent to all users when product quantity is low', function () use ($testingContext) {
    Notification::fake();
    Config::set('stock.alert_min_quantity', 5);
    $this->freezeTime();
    /** @var Product $product */
    $product = Product::factory()->trashed()->create(['quantity' => 30]);
    /** @var User $user */
    $user = UserFactory::new()->create();
    $formData = $testingContext::getGoodFormData();

    $this->assertTrue($product->trashed());
    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 1);

    $formData['quantity'] = 20;
    $response = $this
        ->actingAs($user)
        ->patchJson(route($testingContext::ROUTE, ['product' => $product]), $formData)
        ->assertSuccessful();

    $product->refresh();
    $this->assertTrue($product->quantity > Config::get('stock.alert_min_quantity'));
    Notification::assertNothingSent();
    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 2);

    $formData['quantity'] = 4;
    $response = $this
        ->actingAs($user)
        ->patchJson(route($testingContext::ROUTE, ['product' => $product]), $formData)
        ->assertSuccessful();

    $product->refresh();
    $this->assertTrue($product->quantity < Config::get('stock.alert_min_quantity'));
    Notification::assertSentTimes(ProductQuantityLowNotification::class, User::query()->count());
    //Notification::assertSentTimes();
    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 3);
});

test('nothing happens if there is nothing changed', function () use ($testingContext) {
    Notification::fake();
    $this->freezeTime();
    /** @var Product $product */
    $product = ProductFactory::new()->create(['quantity' => 30]);
    /** @var Product $productBefore */
    $productBefore = $product->replicate();
    /** @var User $user */
    $user = UserFactory::new()->create();

    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 1);

    $formData = $product->getOriginal();

    $response = $this
        ->actingAs($user)
        ->patchJson(route($testingContext::ROUTE, ['product' => $product]), $formData);

    $product->refresh();
    $response->assertSuccessful();
    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 1);

    $this->assertEquals($product->created_at, $product->updated_at);
    $this->assertDatabaseHas(Product::class, $productBefore->getAttributes());
    Notification::assertNothingSent();
});

test('a product cannot be updated with wrong data', function (array $wrongData) use ($testingContext) {
    /** @var Product $product */
    $product = ProductFactory::new()->create(['quantity' => 30]);
    /** @var Product $productBefore */
    $productBefore = $product->replicate();
    /** @var User $user */
    $user = UserFactory::new()->create();
    $formData = array_merge($testingContext::getGoodFormData(), $wrongData);

    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 1);

    $response = $this
        ->actingAs($user)
        ->patchJson(route($testingContext::ROUTE, ['product' => $product]), $formData);

    $response->assertJsonValidationErrors(array_keys($wrongData));

    $this->assertDatabaseCount(Product::class, 1);
    $this->assertDatabaseCount(Movement::class, 1);

    $this->assertEquals($product->created_at, $product->updated_at);
    $this->assertDatabaseHas(Product::class, $productBefore->getAttributes());
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
