<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TokenController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()
            ->tokens()
            ->where('name', '!=', 'spa-session')
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toISOString(),
                'expires_at' => $token->expires_at?->toISOString(),
                'created_at' => $token->created_at->toISOString(),
            ]);

        return $this->successResponse($tokens);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'abilities' => ['sometimes', 'array'],
            'abilities.*' => ['string', Rule::in(['products:read', 'settings:read', 'categories:read'])],
        ]);

        $abilities = $validated['abilities'] ?? ['products:read', 'settings:read', 'categories:read'];

        $token = $request->user()->createToken($validated['name'], $abilities);

        return $this->successResponse([
            'token' => $token->plainTextToken,
            'name' => $validated['name'],
            'abilities' => $abilities,
            'created_at' => now()->toISOString(),
        ], 'Token creado. Cópialo ahora, no se mostrará de nuevo.', 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $token = $request->user()->tokens()->findOrFail($id);

        if ($token->name === 'spa-session') {
            return $this->errorResponse('No se puede revocar el token de sesión activo.', 403);
        }

        $token->delete();

        return $this->successResponse(null, 'Token revocado correctamente.');
    }
}
