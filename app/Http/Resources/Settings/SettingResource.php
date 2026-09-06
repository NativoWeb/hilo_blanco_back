<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key,
            'value' => $this->casted_value,
            'type' => $this->type,
            'label' => $this->label,
            'group' => $this->group,
            'is_public' => $this->is_public,
        ];
    }
}
