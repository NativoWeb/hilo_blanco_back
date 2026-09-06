<?php

namespace App\Http\Resources\Categories;

use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image ? ImageService::publicUrl($this->image) : null,
            'image_thumbnail' => $this->image ? ImageService::thumbnailUrl($this->image) : null,
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'products_count' => $this->whenCounted('products'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
