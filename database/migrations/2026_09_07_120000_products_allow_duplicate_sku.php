<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Permite varios productos con el mismo SKU (un producto por fila del CSV,
 * normalmente una por vehículo compatible).
 *
 * - Se quita el índice UNIQUE de products.sku y se deja un índice normal.
 * - Se agrega products.import_key: clave estable que usa el sincronizador para
 *   saber si una fila del CSV corresponde a un producto ya existente
 *   (sku + marca compatible + modelo compatible). Los productos creados a mano
 *   quedan con import_key = NULL y el importador nunca los toca.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Quitar el UNIQUE de sku (si aún existe con el nombre por defecto de Laravel).
        $exists = collect(DB::select("SHOW INDEX FROM products WHERE Key_name = 'products_sku_unique'"))->isNotEmpty();
        if ($exists) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique('products_sku_unique');
            });
        }

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'import_key')) {
                $table->string('import_key')->nullable()->after('sku');
            }
        });

        // 2. Índices de apoyo (con guardas por si la migración se re-ejecuta).
        $hasSkuIndex = collect(DB::select("SHOW INDEX FROM products WHERE Key_name = 'products_sku_index'"))->isNotEmpty();
        if (! $hasSkuIndex) {
            Schema::table('products', fn (Blueprint $table) => $table->index('sku'));
        }

        $hasImportKeyIndex = collect(DB::select("SHOW INDEX FROM products WHERE Key_name = 'products_import_key_index'"))->isNotEmpty();
        if (! $hasImportKeyIndex) {
            Schema::table('products', fn (Blueprint $table) => $table->index('import_key'));
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_import_key_index');
            $table->dropIndex('products_sku_index');
            $table->dropColumn('import_key');
        });

        // Ojo: si ya hay SKUs duplicados en la tabla esto fallará (es lo esperado:
        // primero hay que consolidar los duplicados a mano).
        Schema::table('products', function (Blueprint $table) {
            $table->unique('sku');
        });
    }
};
