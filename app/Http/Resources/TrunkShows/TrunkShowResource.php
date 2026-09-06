<?php

namespace App\Http\Resources\TrunkShows;

use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrunkShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'city' => $this->city,
            'location' => $this->location,
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date->toDateString(),
            'schedule' => $this->schedule,
            'session_duration' => $this->session_duration,
            'max_guests' => $this->max_guests,
            'image' => $this->image ? ImageService::publicUrl($this->image) : null,
            'image_thumbnail' => $this->image ? ImageService::thumbnailUrl($this->image) : null,
            'is_upcoming' => $this->isUpcoming(),
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
