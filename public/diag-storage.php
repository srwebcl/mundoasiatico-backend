<?php
if (($_GET['token'] ?? '') !== 'ma2026diag') { http_response_code(403); die('Forbidden'); }
header('Content-Type: text/plain; charset=utf-8');

$pub = __DIR__;                                          // /home2/.../public
$storageLink = $pub . '/storage';                        // public/storage
$storageReal = dirname($pub) . '/storage/app/public';    // storage/app/public

echo "=== DIAGNÓSTICO DE STORAGE ===\n\n";

// 1. ¿Qué ES public/storage?
echo "1. public/storage existe?     " . (file_exists($storageLink) ? "SÍ" : "NO") . "\n";
echo "   Es symlink?                " . (is_link($storageLink) ? "SÍ" : "NO") . "\n";
echo "   Es directorio real?        " . (is_dir($storageLink) && !is_link($storageLink) ? "SÍ" : "NO") . "\n";
if (is_link($storageLink)) {
    echo "   Apunta a:                 " . readlink($storageLink) . "\n";
    echo "   Symlink resolvible?       " . (realpath($storageLink) ? "SÍ → " . realpath($storageLink) : "NO (roto)") . "\n";
}
echo "   Permisos public/storage:   " . (file_exists($storageLink) ? substr(sprintf('%o', fileperms($storageLink)), -4) : "N/A") . "\n";

// 2. ¿Existe el directorio real storage/app/public?
echo "\n2. storage/app/public existe? " . (is_dir($storageReal) ? "SÍ" : "NO") . "\n";
if (is_dir($storageReal)) {
    echo "   Permisos:                 " . substr(sprintf('%o', fileperms($storageReal)), -4) . "\n";
    $files = glob($storageReal . '/**/*.*', GLOB_BRACE) ?: [];
    $files = array_merge($files, glob($storageReal . '/*.*') ?: []);
    echo "   Archivos encontrados:     " . count($files) . "\n";
    foreach (array_slice($files, 0, 5) as $f) {
        echo "     - " . str_replace($storageReal, '', $f) . " (" . substr(sprintf('%o', fileperms($f)), -4) . ")\n";
    }
}

// 3. ¿Qué hay en public/storage directamente?
echo "\n3. Contenido public/storage/:\n";
if (file_exists($storageLink)) {
    $items = scandir($storageLink) ?: [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $storageLink . '/' . $item;
        echo "   " . $item . " (" . (is_dir($path) ? "DIR" : "FILE") . ", " . substr(sprintf('%o', fileperms($path)), -4) . ")\n";
    }
} else {
    echo "   (no existe)\n";
}

// 4. Buscar un archivo específico conocido
$testFile = '01KP8KKPT39VVY653PA9ZJX825.jpeg';
$possiblePaths = [
    $pub . '/storage/products/' . $testFile,
    $pub . '/storage/public/products/' . $testFile,
    $storageReal . '/products/' . $testFile,
];
echo "\n4. Buscando archivo de prueba ($testFile):\n";
foreach ($possiblePaths as $p) {
    echo "   " . str_replace(dirname($pub), '', $p) . ": " . (file_exists($p) ? "✓ EXISTE" : "✗ No") . "\n";
}

// 5. .htaccess en public/
echo "\n5. .htaccess en public/:\n";
$htaccess = $pub . '/.htaccess';
if (file_exists($htaccess)) {
    echo file_get_contents($htaccess);
} else {
    echo "   (no existe)\n";
}

echo "\n=== FIN DIAGNÓSTICO ===\n";
