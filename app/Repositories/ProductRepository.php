<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Events\ProductQuantityLow;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductRepository
{
    public function __construct(protected readonly Product $product)
    {
    }

    /**
     * @return Builder<Product>
     */
    public function baseQuery(): Builder
    {
        return $this->product->newQuery();
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public function create(array $data, ?User $user = null): Product
    {
        DB::beginTransaction();
        /** @var Product $product */
        $product = $this->baseQuery()->create($data);
        $product->movements()->create([
            'user_id' => $user?->id,
            'quantity' => $product->quantity,
            'price' => $product->price,
        ]);
        DB::commit();

        return $product;
    }

    /**
     * @param  array<string,mixed>  $data
     */
    public function update(Product $product, array $data, ?User $user = null): Product
    {
        DB::beginTransaction();

        $product->fill($data);

        if ($product->isDirty()) {
            $product->save();
        } else {
            DB::rollBack();

            return $product;
        }

        if ($product->wasChanged(['quantity', 'price'])) {
            $product->movements()->create([
                'user_id' => $user?->id,
                'quantity' => $product->quantity,
                'price' => $product->price,
            ]);
        }

        ProductQuantityLow::dispatchIf(
            $product->wasChanged(['quantity']) && $product->quantity < config('stock.alert_min_quantity'),
            $product
        );

        DB::commit();

        return $product;
    }
}
