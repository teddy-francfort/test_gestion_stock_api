<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        Relation::enforceMorphMap([
            'user' => User::class,
            'movement' => Movement::class,
            'product' => Product::class,
        ]);
        Model::shouldBeStrict(! $this->app->isProduction());
    }
}
