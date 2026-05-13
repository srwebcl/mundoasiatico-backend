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
        $now = now();
        DB::table('settings')->insertOrIgnore([
            ['key' => 'promo_bar_text_color', 'value' => '#ffffff', 'label' => 'Color del Texto', 'type' => 'color', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'promo_bar_url', 'value' => '', 'label' => 'Enlace (URL)', 'type' => 'text', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'promo_bar_icon', 'value' => 'heroicon-o-truck', 'label' => 'Icono', 'type' => 'text', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'promo_bar_font_weight', 'value' => 'bold', 'label' => 'Grosor de Fuente', 'type' => 'text', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'promo_bar_start_at', 'value' => null, 'label' => 'Fecha de Inicio', 'type' => 'datetime', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'promo_bar_end_at', 'value' => null, 'label' => 'Fecha de Fin', 'type' => 'datetime', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'promo_bar_animate', 'value' => '0', 'label' => 'Animar (Pulso)', 'type' => 'boolean', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'promo_bar_text_color', 'promo_bar_url', 'promo_bar_icon', 
            'promo_bar_font_weight', 'promo_bar_start_at', 'promo_bar_end_at', 'promo_bar_animate'
        ])->delete();
    }
};
