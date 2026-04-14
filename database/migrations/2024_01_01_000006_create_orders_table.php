<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Cliente (nullable para compras como invitado)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Datos del cliente (guardados al momento del pedido, por si cambian en el futuro)
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 20)->nullable();
            $table->string('customer_rut', 12)->nullable();

            // Estado del Pedido
            $table->enum('status', ['pending', 'paid', 'failed', 'shipped', 'cancelled'])
                  ->default('pending');

            // Montos
            $table->unsignedBigInteger('total_amount');

            // Logística
            $table->enum('shipping_type', ['retiro_stgo', 'retiro_pm', 'starken']);
            $table->json('shipping_address')->nullable(); // Solo si es starken

            // Transbank
            $table->string('transbank_token', 128)->nullable()->index();
            $table->string('transbank_authorization_code', 64)->nullable();
            $table->string('transbank_transaction_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
