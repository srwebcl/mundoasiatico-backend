<?php
if (($_GET['token'] ?? '') !== 'ma2026upload') { http_response_code(403); die('Forbidden'); }

$targetBase = dirname(__DIR__) . '/storage/app/public';

// Manejar subida de ZIP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['zipfile'])) {
    $zip = new ZipArchive();
    $tmpFile = $_FILES['zipfile']['tmp_name'];

    if ($zip->open($tmpFile) === true) {
        $extracted = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            // Saltar directorios y el .gitignore
            if (substr($name, -1) === '/' || strpos($name, '.gitignore') !== false) continue;

            // Quitar el prefijo "public/" del ZIP si existe
            $relativePath = preg_replace('#^public/#', '', $name);
            $destination = $targetBase . '/' . $relativePath;

            // Crear directorio si no existe
            $dir = dirname($destination);
            if (!is_dir($dir)) mkdir($dir, 0775, true);

            // Extraer archivo
            $content = $zip->getFromIndex($i);
            if (file_put_contents($destination, $content) !== false) {
                $extracted++;
            }
        }
        $zip->close();

        header('Content-Type: text/plain');
        echo "✓ Extraídos: $extracted archivos\n\n";
        echo "Contenido de storage/app/public/:\n";
        $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($targetBase));
        foreach ($iter as $file) {
            if ($file->isFile()) echo "  " . str_replace($targetBase, '', $file->getPathname()) . "\n";
        }
        exit;
    } else {
        echo "Error abriendo ZIP";
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Upload Storage — Mundo Asiático</title>
    <style>
        body { font-family: monospace; background: #1a1a2e; color: #eee; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; }
        .box { background:#16213e; border:1px solid #0f3460; border-radius:12px; padding:40px; text-align:center; max-width:500px; }
        h2 { color:#e94560; margin-bottom:20px; }
        input[type=file] { display:block; margin:20px auto; color:#aaa; }
        button { background:#e94560; color:#fff; border:none; padding:12px 30px; border-radius:8px; cursor:pointer; font-size:16px; margin-top:10px; }
        button:hover { background:#c73652; }
        p { color:#aaa; font-size:13px; }
    </style>
</head>
<body>
<div class="box">
    <h2>⬆️ Upload Storage</h2>
    <p>Sube el archivo <strong>mundoasiatico_storage.zip</strong><br>Se extraerá directamente a <code>storage/app/public/</code></p>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="zipfile" accept=".zip" required>
        <button type="submit">Subir y Extraer</button>
    </form>
    <p style="margin-top:20px;color:#555;">⚠️ Eliminar este archivo después de usar</p>
</div>
</body>
</html>
