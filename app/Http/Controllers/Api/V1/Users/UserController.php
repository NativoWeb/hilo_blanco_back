<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\Users\UserCollection;
use App\Http\Resources\Users\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')->paginate(15);

        return $this->paginatedResponse($users, new UserCollection($users));
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return $this->successResponse(new UserResource($user->load('roles')));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();
        $role = $data['role'];
        unset($data['role']);

        $user = User::create($data);
        $user->assignRole($role);

        return $this->successResponse(
            new UserResource($user->load('roles')),
            'Usuario creado correctamente.',
            201
        );
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
            unset($data['role']);
        }

        if (! empty($data)) {
            $user->update($data);
        }

        return $this->successResponse(
            new UserResource($user->fresh(['roles'])),
            'Usuario actualizado correctamente.'
        );
    }

    public function toggleStatus(User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $user->update(['status' => $user->status === 1 ? 0 : 1]);

        $estado = $user->fresh()->status === 1 ? 'activado' : 'desactivado';

        return $this->successResponse(
            new UserResource($user->fresh(['roles'])),
            "Usuario {$estado} correctamente."
        );
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->update(['status' => 0]);

        return $this->successResponse(null, 'Usuario desactivado correctamente.');
    }
}
