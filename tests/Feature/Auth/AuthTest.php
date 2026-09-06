<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin']);
    Role::firstOrCreate(['name' => 'admin']);
});

it('permite login con credenciales correctas', function () {
    $user = User::factory()->create(['password' => 'password123', 'status' => 1]);
    $user->assignRole('admin');

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['success', 'data' => ['user', 'token', 'expires_at']])
        ->assertJson(['success' => true]);
});

it('rechaza login con credenciales incorrectas', function () {
    $user = User::factory()->create(['status' => 1]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertUnauthorized()
        ->assertJson(['success' => false]);
});

it('valida campos requeridos en login', function () {
    $response = $this->postJson('/api/v1/auth/login', []);

    $response->assertUnprocessable()
        ->assertJsonStructure(['errors' => ['email', 'password']]);
});

it('permite logout al usuario autenticado', function () {
    $user = User::factory()->create(['status' => 1]);
    $user->assignRole('admin');
    $token = $user->createToken('spa-session')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/v1/auth/logout');

    $response->assertOk()->assertJson(['success' => true]);
    expect($user->fresh()->tokens()->count())->toBe(0);
});

it('retorna datos del usuario autenticado en /me', function () {
    $user = User::factory()->create(['status' => 1]);
    $user->assignRole('admin');
    $token = $user->createToken('spa-session')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/v1/auth/me');

    $response->assertOk()
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonPath('data.roles.0', 'admin');
});
