<?php

namespace App\Http\Resources\Products;

use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => ImageService::publicUrl($this->path),
            'thumbnail_url' => ImageService::thumbnailUrl($this->path),
            'alt_text' => $this->alt_text,
            'is_cover' => $this->is_cover,
            'sort_order' => $this->sort_order,
        ];
    }
}
