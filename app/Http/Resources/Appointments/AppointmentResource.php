<?php

namespace App\Http\Resources\Appointments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'nombre'     => $this->nombre,
            'whatsapp'   => $this->whatsapp,
            'email'      => $this->email,
            'fecha_boda' => $this->fecha_boda?->format('Y-m-d'),
            'mensaje'    => $this->mensaje,
            'estado'     => $this->estado,
            'notas'      => $this->notas,
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
