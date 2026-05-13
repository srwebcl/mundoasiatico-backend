<?php
/**
 * Script temporal de instalación — Mundo Asiático
 * IMPORTANTE: Eliminar este archivo del servidor después de usarlo.
 * Acceder en: https://api.mundoasiatico.cl/setup.php?token=ma2026setup
 */

// ── Seguridad básica ─────────────────────────────────────────────────────────
if (!isset($_GET['token']) || $_GET['token'] !== 'ma2026setup') {
    http_response_code(403);
    die('Acceso denegado.');
}

// ── Configuración ────────────────────────────────────────────────────────────
$projectRoot = dirname(__DIR__);
set_time_limit(300); // 5 minutos máximo
ini_set('memory_limit', '512M');

// ── Helpers ──────────────────────────────────────────────────────────────────
function titulo($texto) {
    echo "\n<br><strong style='color:#2563eb;font-size:1.1em;'>═══ " . htmlspecialchars($texto) . " ═══</strong><br>\n";
    flush();
    ob_flush();
}

function runCmd($cmd) {
    echo "<span style='color:#6b7280;'>$ " . htmlspecialchars($cmd) . "</span><br>\n";
    flush();
    ob_flush();

    if (!function_exists('exec')) {
        echo "<span style='color:#dc2626;'>✗ exec() deshabilitado en este servidor.</span><br>\n";
        return false;
    }

    $output = [];
    $returnCode = 0;
    exec($cmd . ' 2>&1', $output, $returnCode);

    foreach ($output as $line) {
        $color = str_contains($line, 'error') || str_contains($line, 'Error') ? '#dc2626' : '#166534';
        echo "<span style='color:{$color};'>" . htmlspecialchars($line) . "</span><br>\n";
    }

    if ($returnCode === 0) {
        echo "<span style='color:#15803d;'>✓ Completado (exit: 0)</span><br>\n";
    } else {
        echo "<span style='color:#dc2626;'>✗ Error (exit: {$returnCode})</span><br>\n";
    }

    flush();
    ob_flush();
    return $returnCode === 0;
}

// ── HTML ─────────────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Setup — Mundo Asiático</title>
    <style>
        body { font-family: monospace; background: #0f172a; color: #e2e8f0; padding: 2rem; font-size: 14px; }
        h1 { color: #f8fafc; }
        .warning { background: #7c2d12; border: 1px solid #dc2626; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    </style>
</head>
<body>
<h1>🚀 Setup — Mundo Asiático Backend</h1>
<div class="warning">⚠️ <strong>IMPORTANTE:</strong> Elimina este archivo del servidor después de completar el setup.</div>
<hr>
<pre style="white-space:pre-wrap;">
<?php

// ── Verificaciones ───────────────────────────────────────────────────────────
titulo('Verificando entorno');
echo "PHP version: " . PHP_VERSION . "<br>\n";
echo "Directorio proyecto: {$projectRoot}<br>\n";
echo "exec() disponible: " . (function_exists('exec') ? '<span style="color:#15803d;">✓ SÍ</span>' : '<span style="color:#dc2626;">✗ NO — contacta al hosting</span>') . "<br>\n";
echo ".env existe: " . (file_exists($projectRoot . '/.env') ? '<span style="color:#15803d;">✓ SÍ</span>' : '<span style="color:#dc2626;">✗ NO — crea el .env primero</span>') . "<br>\n";
echo "vendor/ existe: " . (is_dir($projectRoot . '/vendor') ? '<span style="color:#15803d;">✓ SÍ</span>' : '<span style="color:#f59e0b;">⚠ NO — se instalará ahora</span>') . "<br>\n";

if (!function_exists('exec')) {
    echo "\n<span style='color:#dc2626;font-weight:bold;'>✗ No se puede continuar: exec() está deshabilitado.</span><br>\n";
    echo "Solicita a tu hosting que habilite exec() o que te den acceso SSH.<br>\n";
    echo "</pre></body></html>";
    exit;
}

// ── Variables de entorno para Composer ───────────────────────────────────────
$homeDir      = '/home2/luisyane';
$composerHome = '/home2/luisyane/.composer';
$phpBin       = trim(shell_exec('which php') ?: 'php');
$envPrefix    = "HOME={$homeDir} COMPOSER_HOME={$composerHome}";

titulo('Entorno detectado');
echo "PHP binario: {$phpBin}<br>\n";
echo "HOME: {$homeDir}<br>\n";
echo "COMPOSER_HOME: {$composerHome}<br>\n";

// ── Paso 1: Descargar Composer ───────────────────────────────────────────────
titulo('Paso 1 — Descargar Composer');
if (file_exists($projectRoot . '/composer.phar')) {
    echo "composer.phar ya existe, saltando descarga.<br>\n";
} else {
    runCmd("cd {$projectRoot} && curl -sS https://getcomposer.org/installer -o composer-setup.php");
    runCmd("cd {$projectRoot} && {$envPrefix} {$phpBin} composer-setup.php");
    runCmd("cd {$projectRoot} && rm -f composer-setup.php");
}

// ── Paso 2: Instalar dependencias ────────────────────────────────────────────
titulo('Paso 2 — Composer Install (puede tardar 2-3 minutos)');
runCmd("cd {$projectRoot} && {$envPrefix} {$phpBin} composer.phar install --no-dev --optimize-autoloader --no-interaction");

// ── Paso 3: Migraciones ──────────────────────────────────────────────────────
titulo('Paso 3 — Migraciones de base de datos');
runCmd("cd {$projectRoot} && php artisan migrate --force");

// ── Paso 4: Caché de configuración ───────────────────────────────────────────
titulo('Paso 4 — Optimización Laravel');
runCmd("cd {$projectRoot} && php artisan config:cache");
runCmd("cd {$projectRoot} && php artisan route:cache");
runCmd("cd {$projectRoot} && php artisan view:cache");

// ── Paso 5: Storage link ─────────────────────────────────────────────────────
titulo('Paso 5 — Storage Link');
runCmd("cd {$projectRoot} && php artisan storage:link");

// ── Paso 6: Permisos ─────────────────────────────────────────────────────────
titulo('Paso 6 — Permisos de carpetas');
runCmd("chmod -R 775 {$projectRoot}/storage");
runCmd("chmod -R 775 {$projectRoot}/bootstrap/cache");

// ── Paso 7: Usuario admin Filament ───────────────────────────────────────────
titulo('Paso 7 — Crear usuario admin Filament');
$name     = 'Admin Mundo Asiático';
$email    = 'admin@mundoasiatico.cl';
$password = 'Admin.2025!';
$hash     = password_hash($password, PASSWORD_BCRYPT);

runCmd("cd {$projectRoot} && php artisan tinker --execute=\"\\App\\Models\\User::updateOrCreate(['email' => '{$email}'], ['name' => '{$name}', 'password' => '{$hash}', 'role' => 'admin']);\"");

titulo('✅ Setup completado');
echo "Verifica en: <a href='https://api.mundoasiatico.cl/api/products' style='color:#60a5fa;'>https://api.mundoasiatico.cl/api/products</a><br>\n";
echo "Panel admin: <a href='https://api.mundoasiatico.cl/admin' style='color:#60a5fa;'>https://api.mundoasiatico.cl/admin</a><br>\n";
echo "<br><strong style='color:#dc2626;'>⚠ ELIMINA setup.php del servidor ahora desde File Manager.</strong><br>\n";
?>
</pre>
</body>
</html>
