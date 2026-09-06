<?php

use App\Http\Controllers\Api\V1\Appointments\AppointmentController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\TokenController;
use App\Http\Controllers\Api\V1\Categories\CategoryController;
use App\Http\Controllers\Api\V1\Products\ProductController;
use App\Http\Controllers\Api\V1\Settings\SettingController;
use App\Http\Controllers\Api\V1\TrunkShows\TrunkShowController;
use App\Http\Controllers\Api\V1\Users\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas — sin autenticación
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1');

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{slug}', [ProductController::class, 'show']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{slug}', [CategoryController::class, 'show']);

    Route::get('settings/public', [SettingController::class, 'public']);

    // Trunk Shows — listado público
    Route::get('trunk-shows', [TrunkShowController::class, 'index']);
    Route::get('trunk-shows/{slug}', [TrunkShowController::class, 'show']);

    // Citas — POST público (cualquier visitante puede solicitar cita)
    Route::post('appointments', [AppointmentController::class, 'store'])
        ->middleware('throttle:5,1'); // máximo 5 solicitudes por minuto
});

/*
|--------------------------------------------------------------------------
| Rutas autenticadas — requieren token Sanctum
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    // Auth básico (cualquier usuario autenticado)
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    /*
    |----------------------------------------------------------------------
    | Rutas para admin y super_admin
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin|super_admin')->group(function () {
        // Citas — gestión admin
        Route::get('appointments', [AppointmentController::class, 'index']);
        Route::get('appointments/{appointment}', [AppointmentController::class, 'show']);
        Route::patch('appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy']);

        Route::apiResource('categories', CategoryController::class)
            ->only(['store', 'update']);

        Route::apiResource('products', ProductController::class)
            ->only(['store', 'update']);

        Route::post('products/{product}/images', [ProductController::class, 'uploadImages']);
        Route::put('products/{product}/images/{image}', [ProductController::class, 'updateImage']);
        Route::delete('products/{product}/images/{image}', [ProductController::class, 'destroyImage']);
        Route::patch('products/{product}/images/{image}/cover', [ProductController::class, 'setCover']);

        Route::post('categories/{category}/image', [CategoryController::class, 'uploadImage']);
        Route::delete('categories/{category}/image', [CategoryController::class, 'destroyImage']);

        Route::get('settings', [SettingController::class, 'index']);

        // Trunk Shows — gestión admin
        Route::post('trunk-shows', [TrunkShowController::class, 'store']);
        Route::put('trunk-shows/{trunkShow}', [TrunkShowController::class, 'update']);
        Route::delete('trunk-shows/{trunkShow}', [TrunkShowController::class, 'destroy']);
        Route::post('trunk-shows/{trunkShow}/image', [TrunkShowController::class, 'uploadImage']);
        Route::delete('trunk-shows/{trunkShow}/image', [TrunkShowController::class, 'destroyImage']);
    });

    /*
    |----------------------------------------------------------------------
    | Rutas exclusivas de super_admin
    |----------------------------------------------------------------------
    */
    Route::middleware('role:super_admin')->group(function () {
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);

        Route::apiResource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus']);

        Route::apiResource('tokens', TokenController::class)
            ->only(['index', 'store', 'destroy']);

        // Ruta estática ANTES de la paramétrica (buena práctica defensiva)
        Route::patch('settings/bulk', [SettingController::class, 'bulk']);
        Route::post('settings/{key}/media', [SettingController::class, 'uploadMedia']);
        Route::put('settings/{key}', [SettingController::class, 'update']);
    });
});
