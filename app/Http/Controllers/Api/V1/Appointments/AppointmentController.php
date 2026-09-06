<?php

namespace App\Http\Controllers\Api\V1\Appointments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointments\StoreAppointmentRequest;
use App\Http\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    use ApiResponse;

    /** Pública — cualquier visitante puede enviar una solicitud */
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $appointment = Appointment::create($request->validated());

        return $this->successResponse(
            new AppointmentResource($appointment),
            'Solicitud de cita recibida. Nos pondremos en contacto pronto.',
            201
        );
    }

    /** Admin — lista de solicitudes */
    public function index(Request $request): JsonResponse
    {
        $query = Appointment::query()
            ->when($request->estado, fn ($q) => $q->where('estado', $request->estado))
            ->orderByDesc('created_at');

        $appointments = $query->paginate(20);

        return $this->paginatedResponse($appointments, AppointmentResource::collection($appointments));
    }

    /** Admin — ver una solicitud */
    public function show(Appointment $appointment): JsonResponse
    {
        return $this->successResponse(new AppointmentResource($appointment));
    }

    /** Admin — cambiar estado o agregar notas */
    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        $validated = $request->validate([
            'estado' => ['sometimes', 'in:pendiente,confirmada,cancelada'],
            'notas'  => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $appointment->update($validated);

        return $this->successResponse(
            new AppointmentResource($appointment->fresh()),
            'Cita actualizada correctamente.'
        );
    }

    /** Admin — eliminar */
    public function destroy(Appointment $appointment): JsonResponse
    {
        $appointment->delete();

        return $this->successResponse(null, 'Solicitud eliminada.');
    }
}
