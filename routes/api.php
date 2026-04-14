<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Mundo Asiático
|--------------------------------------------------------------------------
| Todas las rutas devuelven JSON.
| La autenticación se maneja con Laravel Sanctum (tokens).
|--------------------------------------------------------------------------
*/

// ── Rutas PÚBLICAS ────────────────────────────────────────────────────────────

// Catálogo
Route::get('/products',         [ProductController::class,  'index']);
Route::get('/products/{slug}',  [ProductController::class,  'show']);
Route::get('/categories',       [CategoryController::class, 'index']);
Route::get('/brands',           [BrandController::class,    'index']);

// Autenticación
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Checkout (disponible sin login para invitados)
// El precio aplicado (retail vs mayorista) depende del token Sanctum si se envía.
Route::post('/checkout/init',   [CheckoutController::class, 'init']);
Route::post('/checkout/return', [CheckoutController::class, 'return']); // Webhook Transbank

// ── Rutas PROTEGIDAS (requieren token Sanctum) ────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Perfil del usuario autenticado
    Route::prefix('auth')->group(function () {
        Route::get('/me',     [AuthController::class, 'me']);
        Route::post('/logout',[AuthController::class, 'logout']);
    });

    // Consulta de orden (para página /exito)
    Route::get('/orders/{id}', [CheckoutController::class, 'show']);
});
