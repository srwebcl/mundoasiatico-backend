<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_number')->nullable()->after('shipping_address');
            $table->string('shipping_carrier')->nullable()->after('tracking_number'); // starken, chilexpress, bluexpress
            $table->timestamp('shipped_at')->nullable()->after('shipping_carrier');
            $table->string('coupon_code')->nullable()->after('shipped_at');
            $table->integer('discount_amount')->default(0)->after('coupon_code'); // en pesos
            $table->text('admin_notes')->nullable()->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'tracking_number', 'shipping_carrier', 'shipped_at',
                'coupon_code', 'discount_amount', 'admin_notes',
            ]);
        });
    }
};
