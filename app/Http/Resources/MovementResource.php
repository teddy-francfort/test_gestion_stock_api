<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Movement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovementResource extends JsonResource
{
    /** @var Movement */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'price' => $this->resource->price,
            'quantity' => $this->resource->quantity,
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'user' => UserResource::make($this->whenLoaded('user')),
        ];
    }
}
