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
            ['label' => 'Número de WhatsApp Front', 'value' => '56971602029', 'type' => 'text']
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
