<?php

namespace App\Http\Resources\Products;

use App\Http\Resources\Categories\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'details' => $this->details,
            'price' => $this->price,
            'is_featured' => $this->is_featured,
            'is_customizable' => $this->is_customizable,
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'cover_image' => $this->when(
                $this->relationLoaded('images'),
                fn () => $this->images->firstWhere('is_cover', true)
                    ? new ProductImageResource($this->images->firstWhere('is_cover', true))
                    : null
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
