<?php
/**
 * Script temporal para resetear contraseña admin.
 * ELIMINAR DESPUÉS DE USAR.
 * URL: https://api.mundoasiatico.cl/reset-admin.php?token=ma2026reset
 */

if (!isset($_GET['token']) || $_GET['token'] !== 'ma2026reset') {
    http_response_code(403);
    die('Acceso denegado.');
}

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$email    = 'admin@mundoasiatico.cl';
$password = 'Admin.2025!';

// Actualiza la contraseña directamente en la BD (bypasa el mutador)
$updated = DB::table('users')
    ->where('email', $email)
    ->update([
        'password'   => Hash::make($password),
        'updated_at' => now(),
    ]);

if ($updated) {
    echo "✅ Contraseña actualizada correctamente para: {$email}<br>";
    echo "🔑 Nueva contraseña: {$password}<br>";
    echo "<br>⚠️ <strong>Elimina este archivo del servidor ahora.</strong>";
} else {
    // Si no existe el usuario, lo crea
    DB::table('users')->insert([
        'name'       => 'Admin Mundo Asiático',
        'email'      => $email,
        'password'   => Hash::make($password),
        'role'       => 'admin',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✅ Usuario admin creado: {$email}<br>";
    echo "🔑 Contraseña: {$password}<br>";
    echo "<br>⚠️ <strong>Elimina este archivo del servidor ahora.</strong>";
}
