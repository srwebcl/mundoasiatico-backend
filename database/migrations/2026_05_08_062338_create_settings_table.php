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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('label')->nullable();
            $table->string('type')->default('text'); // text, boolean, color, etc.
            $table->timestamps();
        });

        // Insertar valores por defecto para la barra promocional
        DB::table('settings')->insert([
            [
                'key' => 'promo_bar_text',
                'value' => '🚚 Despacho GRATIS por compras sobre $100.000',
                'label' => 'Texto de la Barra Promocional',
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'promo_bar_enabled',
                'value' => '1',
                'label' => 'Mostrar Barra Promocional',
                'type' => 'boolean',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
