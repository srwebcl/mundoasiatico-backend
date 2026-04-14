<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // Ej: "Chery Tiggo 2"
            $table->string('slug')->unique();
            $table->foreignId('brand_id')                // Marca del VEHÍCULO (Chery, MG, JAC...)
                  ->constrained('brands')
                  ->cascadeOnDelete();
            $table->unsignedSmallInteger('year_start')->nullable(); // Ej: 2018
            $table->unsignedSmallInteger('year_end')->nullable();   // Ej: 2023 (null = vigente)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_models');
    }
};
