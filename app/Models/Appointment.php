<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'nombre',
        'whatsapp',
        'email',
        'fecha_boda',
        'mensaje',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha_boda' => 'date',
    ];
}
