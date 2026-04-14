<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('regular_price');    // Precio en CLP (sin decimales)
            $table->unsignedBigInteger('wholesale_price');  // Precio mayorista en CLP
            $table->unsignedInteger('stock')->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            // Relaciones
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->restrictOnDelete();
            $table->foreignId('brand_id')               // Marca del REPUESTO (OEM, valeo, etc.) — puede ser null
                  ->nullable()
                  ->constrained('brands')
                  ->nullOnDelete();

            $table->timestamps();
        });

        // Tabla Pivote: producto ↔ modelos de autos compatibles
        Schema::create('car_model_product', function (Blueprint $table) {
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();
            $table->foreignId('car_model_id')
                  ->constrained('car_models')
                  ->cascadeOnDelete();
            $table->primary(['product_id', 'car_model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_model_product');
        Schema::dropIfExists('products');
    }
};
