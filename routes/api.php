<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\MyOrdersController;
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
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',  [AuthController::class, 'resetPassword']);
});

// Cupones
Route::post('/coupons/apply', [CouponController::class, 'apply']);

// Checkout (disponible sin login para invitados)
// El precio aplicado (retail vs mayorista) depende del token Sanctum si se envía.
Route::post('/checkout/init',   [CheckoutController::class, 'init']);
Route::post('/checkout/return', [CheckoutController::class, 'return']); // Webhook Banchile

// Scripts de Marketing Activos
Route::get('/settings/marketing-scripts', function () {
    return \App\Models\MarketingScript::where('is_active', true)->get();
});

// Popups Activos
Route::get('/settings/popups', function () {
    return \App\Models\Popup::where('is_active', true)->get();
});

// Barra Promocional y Ajustes Globales
Route::get('/settings/promo-bar', function () {
    $settings = \App\Models\Setting::where('key', 'like', 'promo_bar_%')
        ->get()
        ->pluck('value', 'key');

    return [
        'enabled'     => ($settings['promo_bar_enabled'] ?? '0') == '1',
        'text'        => $settings['promo_bar_text'] ?? '',
        'url'         => $settings['promo_bar_url'] ?? '',
        'color'       => $settings['promo_bar_color'] ?? '#0a0a0a',
        'text_color'  => $settings['promo_bar_text_color'] ?? '#ffffff',
        'font_weight' => $settings['promo_bar_font_weight'] ?? 'bold',
        'icon'        => $settings['promo_bar_icon'] ?? 'heroicon-o-truck',
        'animate'     => ($settings['promo_bar_animate'] ?? '0') == '1',
        'start_at'    => $settings['promo_bar_start_at'] ?? null,
        'end_at'      => $settings['promo_bar_end_at'] ?? null,
    ];
});

// Capturar Lead de WhatsApp a Brevo
Route::post('/settings/whatsapp-lead', [\App\Http\Controllers\Api\WhatsAppLeadController::class, 'store']);

// ── Rutas PROTEGIDAS (requieren token Sanctum) ────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Perfil del usuario autenticado
    Route::prefix('auth')->group(function () {
        Route::get('/me',     [AuthController::class, 'me']);
        Route::post('/logout',[AuthController::class, 'logout']);
    });

    // Consulta de orden (para página /exito)
    Route::get('/orders/{id}', [CheckoutController::class, 'show']);

    // Historial del cliente
    Route::get('/my-orders',      [MyOrdersController::class, 'index']);
    Route::get('/my-orders/{id}', [MyOrdersController::class, 'show']);
});
