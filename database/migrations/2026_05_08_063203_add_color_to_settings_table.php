<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insertar la configuración del color si no existe
        DB::table('settings')->insertOrIgnore([
            [
                'key' => 'promo_bar_color',
                'value' => '#0a0a0a',
                'label' => 'Color de Fondo de la Barra',
                'type' => 'color',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'promo_bar_color')->delete();
    }
};
