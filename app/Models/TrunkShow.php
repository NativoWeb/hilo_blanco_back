<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrunkShow extends Model
{
    protected $fillable = [
        'title', 'slug', 'subtitle', 'description',
        'city', 'location', 'start_date', 'end_date',
        'schedule', 'session_duration', 'max_guests',
        'image', 'status', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'session_duration' => 'integer',
            'max_guests' => 'integer',
            'status' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('end_date', '>=', now()->toDateString());
    }

    public function scopePast($query)
    {
        return $query->where('end_date', '<', now()->toDateString());
    }

    public function isUpcoming(): bool
    {
        return $this->end_date >= now()->toDateString();
    }
}
