<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();                    // Ej: VERANO20
            $table->enum('type', ['percent', 'fixed']);          // % o monto fijo
            $table->integer('value');                            // 20 (%) o 5000 (CLP)
            $table->integer('min_amount')->default(0);           // Monto mínimo de compra
            $table->integer('max_uses')->nullable();             // Null = ilimitado
            $table->integer('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
