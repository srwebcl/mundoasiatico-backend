<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // En MySQL, cambiar un ENUM requiere un comando directo o recrear la columna.
        // Usamos DB::statement para mayor precisión.
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'failed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'failed', 'shipped', 'cancelled') DEFAULT 'pending'");
    }
};
