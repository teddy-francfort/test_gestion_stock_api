<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\PriceCast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property positive-int $id
 * @property positive-int|null $user_id
 * @property positive-int $product_id
 * @property float $price
 * @property int $quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Product $product
 */
class Movement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'price',
        'quantity',
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'price' => PriceCast::class,
        'quantity' => 'integer',
    ];

    /**
     * @return BelongsTo<User, Movement>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Product, Movement>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
