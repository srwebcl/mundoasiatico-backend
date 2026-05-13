<?php
if (($_GET['token'] ?? '') !== 'ma2026fix') { http_response_code(403); die('Forbidden'); }
header('Content-Type: text/plain; charset=utf-8');

$publicDir   = __DIR__;                                              // .../public
$storageLink = $publicDir . '/storage';                             // .../public/storage (symlink actual)
$sourceDir   = dirname($publicDir) . '/storage/app/public';         // .../storage/app/public

echo "=== FIX STORAGE SYMLINK → DIRECTORIO REAL ===\n\n";

// 1. Verificar que el source existe con archivos
if (!is_dir($sourceDir)) {
    die("✗ ERROR: $sourceDir no existe\n");
}
$sourceFiles = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS)) as $f) {
    if ($f->isFile()) $sourceFiles[] = $f->getPathname();
}
echo "Archivos en storage/app/public: " . count($sourceFiles) . "\n\n";

// 2. Eliminar el symlink actual
if (is_link($storageLink)) {
    if (unlink($storageLink)) {
        echo "✓ Symlink eliminado\n";
    } else {
        die("✗ No se pudo eliminar el symlink. Permisos insuficientes.\n");
    }
} elseif (is_dir($storageLink)) {
    echo "ℹ Ya es directorio real, limpiando contenido...\n";
}

// 3. Crear directorio real
if (!is_dir($storageLink)) {
    mkdir($storageLink, 0775, true);
    echo "✓ Directorio public/storage/ creado\n";
}

// 4. Copiar todos los archivos
$copied = 0;
$errors = 0;
foreach ($sourceFiles as $src) {
    $relative = str_replace($sourceDir . '/', '', $src);
    $dest = $storageLink . '/' . $relative;
    $destDir = dirname($dest);
    if (!is_dir($destDir)) mkdir($destDir, 0775, true);
    if (copy($src, $dest)) {
        chmod($dest, 0664);
        echo "  ✓ " . $relative . "\n";
        $copied++;
    } else {
        echo "  ✗ ERROR copiando: " . $relative . "\n";
        $errors++;
    }
}

echo "\n=== RESULTADO ===\n";
echo "Copiados: $copied | Errores: $errors\n\n";

// 5. Verificar acceso
$testFile = $storageLink . '/products/01KP8KKPT39VVY653PA9ZJX825.jpeg';
echo "Archivo de prueba existe: " . (file_exists($testFile) ? "SÍ ✓" : "NO ✗") . "\n";
if (file_exists($testFile)) {
    echo "Permisos: " . substr(sprintf('%o', fileperms($testFile)), -4) . "\n";
    echo "\n✅ Listo. Prueba: https://api.mundoasiatico.cl/storage/products/01KP8KKPT39VVY653PA9ZJX825.jpeg\n";
}
echo "\n⚠️  Elimina este archivo del servidor cuando termines.\n";
