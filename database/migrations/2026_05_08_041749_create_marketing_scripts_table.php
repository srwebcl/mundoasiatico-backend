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
        Schema::create('marketing_scripts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: Google Analytics
            $table->string('type')->default('custom'); // gtm, analytics, meta, custom
            $table->text('code'); // El snippet o ID
            $table->enum('placement', ['head', 'body'])->default('head'); // Dónde inyectarlo
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_scripts');
    }
};
