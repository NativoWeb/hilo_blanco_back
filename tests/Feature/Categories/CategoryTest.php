<?php

use App\Models\Category;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin']);
    Role::firstOrCreate(['name' => 'admin']);
});

it('lista categorías activas sin autenticación', function () {
    Category::factory()->count(3)->create(['status' => 1]);
    Category::factory()->create(['status' => 0]);

    $response = $this->getJson('/api/v1/categories');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(3, 'data');
});

it('permite al admin crear una categoría', function () {
    $admin = User::factory()->create(['status' => 1]);
    $admin->assignRole('admin');
    $token = $admin->createToken('spa-session')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/v1/categories', [
        'name' => 'Nueva Colección',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Nueva Colección')
        ->assertJsonPath('data.slug', 'nueva-coleccion');
});

it('solo super_admin puede desactivar categorías', function () {
    $category = Category::factory()->create(['status' => 1]);
    $admin = User::factory()->create(['status' => 1]);
    $admin->assignRole('admin');
    $token = $admin->createToken('spa-session')->plainTextToken;

    $response = $this->withToken($token)->deleteJson("/api/v1/categories/{$category->id}");

    $response->assertForbidden();
    expect($category->fresh()->status)->toBe(1);
});

it('super_admin puede desactivar una categoría', function () {
    $category = Category::factory()->create(['status' => 1]);
    $superAdmin = User::factory()->create(['status' => 1]);
    $superAdmin->assignRole('super_admin');
    $token = $superAdmin->createToken('spa-session')->plainTextToken;

    $response = $this->withToken($token)->deleteJson("/api/v1/categories/{$category->id}");

    $response->assertOk();
    expect($category->fresh()->status)->toBe(0);
});
