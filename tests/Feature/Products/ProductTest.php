<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin']);
    Role::firstOrCreate(['name' => 'admin']);
});

it('lista productos activos sin autenticación', function () {
    $category = Category::factory()->create(['status' => 1]);
    Product::factory()->count(3)->create(['category_id' => $category->id, 'status' => 1]);
    Product::factory()->create(['category_id' => $category->id, 'status' => 0]);

    $response = $this->getJson('/api/v1/products');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(3, 'data');
});

it('requiere autenticación para crear productos', function () {
    $response = $this->postJson('/api/v1/products', []);

    $response->assertUnauthorized();
});

it('permite al admin crear un producto', function () {
    $category = Category::factory()->create(['status' => 1]);
    $admin = User::factory()->create(['status' => 1]);
    $admin->assignRole('admin');
    $token = $admin->createToken('spa-session')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/v1/products', [
        'category_id' => $category->id,
        'sku' => 'TEST-001',
        'name' => 'Vestido de prueba',
        'description' => 'Descripción de prueba',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.sku', 'TEST-001');
});

it('devuelve 403 si un usuario sin rol intenta crear producto', function () {
    $user = User::factory()->create(['status' => 1]);
    $token = $user->createToken('spa-session')->plainTextToken;
    $category = Category::factory()->create(['status' => 1]);

    $response = $this->withToken($token)->postJson('/api/v1/products', [
        'category_id' => $category->id,
        'sku' => 'NO-ROLE-001',
        'name' => 'Sin permiso',
    ]);

    $response->assertForbidden();
});

it('muestra detalle de producto por slug', function () {
    $category = Category::factory()->create(['status' => 1]);
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 1]);

    $response = $this->getJson("/api/v1/products/{$product->slug}");

    $response->assertOk()
        ->assertJsonPath('data.slug', $product->slug);
});
