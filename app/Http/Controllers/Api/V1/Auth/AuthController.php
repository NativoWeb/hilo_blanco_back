<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\UserAuthResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse('Credenciales incorrectas.', 401);
        }

        $user = Auth::user();

        $user->tokens()->where('name', 'spa-session')->delete();

        $token = $user->createToken('spa-session', ['*'], now()->addHours(8));

        return $this->successResponse([
            'user' => new UserAuthResource($user),
            'token' => $token->plainTextToken,
            'expires_at' => now()->addHours(8)->toISOString(),
        ], 'Sesión iniciada correctamente.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Sesión cerrada correctamente.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(new UserAuthResource($request->user()));
    }
}
