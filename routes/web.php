<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// Ruta temporal para ejecutar migraciones y limpiar caché en cPanel
Route::get('/run-setup', function () {
    try {
        // 1. Ejecutar migraciones
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = \Illuminate\Support\Facades\Artisan::output();

        // 2. Limpiar Caché
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        \Illuminate\Support\Facades\Artisan::call('filament:clear-cached-components');
        $optimizeOutput = \Illuminate\Support\Facades\Artisan::output();

        // 3. Restaurar symlink de storage (para que se vean las imágenes)
        try {
            \Illuminate\Support\Facades\Artisan::call('storage:link');
        } catch (\Exception $e) {
            // Silencioso por si ya existe o hay permisos
        }

        // 4. Crear registro de WhatsApp inicial
        \App\Models\Setting::firstOrCreate(
            ['key' => 'whatsapp_number'],
            ['label' => 'Número de WhatsApp Front', 'value' => '56941737497', 'type' => 'text']
        );

        return response()->json([
            'status' => 'success',
            'message' => '¡Configuración completada con éxito!',
            'migrate_output' => $migrateOutput,
            'optimize_output' => $optimizeOutput,
            'symlink_msg' => 'Se intentó crear/restaurar el symlink de storage.',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Ocurrió un error: ' . $e->getMessage()
        ], 500);
    }
});

Route::get('/fix-storage', function () {
    $target = storage_path('app/public');
    $link = public_path('storage');
    
    $msg = "";
    if (file_exists($link) || is_link($link)) {
        if (is_link($link)) {
            unlink($link);
            $msg .= "Symlink anterior eliminado. ";
        } else {
            return 'El directorio public/storage ya existe y es una carpeta real (no un acceso directo). Renómbrala o bórrala manualmente en cPanel para poder crear el enlace.';
        }
    }
    
    try {
        symlink($target, $link);
        $msg .= "Nuevo symlink creado exitosamente de $target a $link.";
        return $msg;
    } catch (\Exception $e) {
        return "Error creando symlink: " . $e->getMessage();
    }
});
Route::get('/run-migrations-secreto-123', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return 'Migraciones ejecutadas correctamente. Resultado:<br><pre>' . \Illuminate\Support\Facades\Artisan::output() . '</pre>';
    } catch (\Exception $e) {
        return 'Error ejecutando migraciones:<br><pre>' . $e->getMessage() . '</pre>';
    }
});
