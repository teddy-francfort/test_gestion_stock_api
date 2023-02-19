<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\PriceCast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property positive-int $id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property int $quantity
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'price' => PriceCast::class,
        'quantity' => 'integer',
    ];

    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
    ];
}
