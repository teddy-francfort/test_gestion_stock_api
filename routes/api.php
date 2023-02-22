<?php

declare(strict_types=1);

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/is-auth', fn () => response()->json());

Route::middleware(['auth:sanctum'])
    ->delete('/products/{product}', [ProductController::class, 'destroy'])
    ->withTrashed()
    ->name('products.destroy');

Route::middleware(['auth:sanctum'])->apiResource('products', ProductController::class)
    ->only(['index', 'show', 'update'])
    ->withTrashed();

Route::middleware(['auth:sanctum'])->apiResource('products', ProductController::class)
    ->only(['store']);
