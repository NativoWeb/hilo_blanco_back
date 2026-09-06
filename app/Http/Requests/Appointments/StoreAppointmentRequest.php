<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'     => ['required', 'string', 'max:200'],
            'whatsapp'   => ['required', 'string', 'max:30'],
            'email'      => ['nullable', 'email', 'max:200'],
            'fecha_boda' => ['nullable', 'date', 'after:today'],
            'mensaje'    => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Error de validación.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
