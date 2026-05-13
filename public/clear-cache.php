<?php
/**
 * clear-cache.php — Limpieza de emergencia de caches de Laravel
 * ⚠️ ELIMINAR DESPUÉS DE USAR
 */

// Seguridad básica
if (($_GET['token'] ?? '') !== 'ma2026clear') {
    http_response_code(403);
    die('Forbidden');
}

$basePath = dirname(__DIR__); // Un nivel arriba de public/
$cacheFiles = [
    $basePath . '/bootstrap/cache/config.php',
    $basePath . '/bootstrap/cache/routes-v7.php',
    $basePath . '/bootstrap/cache/routes.php',
    $basePath . '/bootstrap/cache/packages.php',
    $basePath . '/bootstrap/cache/services.php',
    $basePath . '/bootstrap/cache/events.php',
];

$results = [];
foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            $results[] = "✓ Eliminado: " . basename($file);
        } else {
            $results[] = "✗ Error eliminando: " . basename($file);
        }
    } else {
        $results[] = "— No existe: " . basename($file);
    }
}

// También limpiar views cache
$viewsDir = $basePath . '/storage/framework/views/';
$viewCount = 0;
if (is_dir($viewsDir)) {
    foreach (glob($viewsDir . '*.php') as $f) {
        if (unlink($f)) $viewCount++;
    }
    $results[] = "✓ Views cache limpiada: {$viewCount} archivos";
}

header('Content-Type: text/plain; charset=utf-8');
echo "=== Laravel Cache Clear ===\n\n";
foreach ($results as $r) {
    echo $r . "\n";
}
echo "\n✅ Listo. El siguiente request regenerará los caches necesarios.";
echo "\n⚠️  Elimina este archivo del servidor cuando termines.";
